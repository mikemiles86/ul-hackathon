<?php

namespace AppBundle\Util;

use AppBundle\Util\ULDatabase;
use AppBundle\Util\ULParser;

class ULTaskRunner {

  private $database;
  private $stop_watch;
  private $time_limit = 5;
  private $error_messages = array();
  private $messages = array();

  public function __construct(ULDatabase $database) {
    $this->database = $database;
    return $this;
  }

  public function runMultipleTasks($tasks = array(), $allowed_time = 0) {
    $tasks_run = 0;

    // Get time allowed per task.
    $minimal_time = $this->getMinimalTime($allowed_time, count($tasks));
    $this->startStopWatch('multiple_tasks');
    foreach ($tasks as $task) {
      if (method_exists($this, $task)) {
        if (!$this->$task($minimal_time)) {
          $this->setErrorMessage('all_tasks', 'Error running ' . $task);
        }
        else {
          $tasks_run++;
        }
      }
    }
    $this->stopStopWatch('multiple_tasks');

    if ($tasks_run > 0) {
      $this->setMessage('all_tasks', 'Completed ' . $tasks_run . ' Tasks');
    }

    return $tasks_run;
  }

  public function updateContentDocuments($allowed_time = 0) {
    $update_count = 0;
    // Start watch.
    $this->startStopWatch('update_content');

    // Loop and do as many as possible.
    $offset = 0;
    while (!$this->overAllowedTime('update_content', $allowed_time)) {
      $content_document = false;
      // Find content document that has never been updated
      if ($document = $this->database->findDocuments('content_document',['last_update_date' => null], ['create_date' => 'ASC'], 1, $offset)) {
        $content_document = $document;
      // Else find oldest
      } elseif ($document = $this->database->findDocuments('content_document',[], ['last_update_date' => 'ASC'], 1, $offset)) {
        $content_document = $document;
      } else {
        $this->setErrorMessage('update_content', 'Unable to find Documents to update.');
      }

      // Have a content document && site config?
      if ($content_document) {
        if ($site_config = $this->database->findDocuments('site_config',["_id" => $content_document->getSiteId()],[],1)) {
          // Create a new Parser.
          $parser = new ULParser();
          // Update content document.
          $parser->parseContentDocument($content_document, $site_config);
          // Set update time.
          $content_document->setLastUpdated(time());
          // Save updated.
          $this->database->updateDocument($content_document);
          $update_count++;
        }
        else {
          $this->setErrorMessage('update_content', 'Unable to load Site Config for ' . $content_document->getUrl());
          $offset++;
        }
      }
      // else stop the watch.
      else {
        $this->stopStopWatch('update_content');
      }
    }
    $this->stopStopWatch('update_content');

    if ($update_count > 0) {
      $this->setMessage('update_content', 'Updated ' . $update_count . ' Documents.');
    }

    return $update_count;
  }

  /**
   * Update links in as many sitemaps as possilbe.
   * @param \AppBundle\Util\int|NULL $allowed_time
   */
  public function buildSitemaps($allowed_time = 0) {
    $sitemaps = 0;

    //Start stopwatch
    $this->startStopWatch('build_sitemaps');

    // Loop and do as many as possible.
    $offset = 0;
    while (!$this->overAllowedTime('build_sitemaps', $allowed_time)) {
      $site_config = false;

      // Find oldest site that does not have a site map
      if ($config = $this->database->findDocuments('site_config', ['sitemap' => null], ['last_update_date' => 'ASC'], 1, $offset)) {
        $site_config = $config;
      // Else just find oldest.
      } elseif ($config = $this->database->findDocuments('site_config', [], ['last_update_date' => 'ASC'], 1, $offset)) {
        $site_config = $config;
      }
      // Have a site config?
      if ($site_config) {
        // Get new crawler.
        $crawler = new ULSiteCrawler($this->database, $site_config);

        // Get sitemap from config.
        $sitemap = $site_config->getSitemap();
        // Sitemap not found? create a basic one.
        if (!$sitemap) {
          $sitemap = [['url' => $site_config->getSiteDomain(), 'parent' => '']];
        }

        // Flatten the sitemap.
        $flat_map = $crawler->flattenSitemap($sitemap);
        // Set the known links
        $crawler->setKnownLinks(array_keys($flat_map));

        $new_map = array();
        foreach ($flat_map as $page) {
          if (!$this->overAllowedTime('build_sitemaps', $allowed_time)) {
            $new_map = array_merge($new_map, $crawler->buildFlatMap($page));
          }
        }
        $new_map = array_merge($flat_map, $new_map);

        $new_map = $crawler->inflateSitemap($new_map);
        // Save sitemap.
        $site_config->setSitemap($new_map);
        // Update last update date.
        $site_config->setLastUpdateDate(time());
        // Save site config.
        $this->database->updateDocument($site_config);
        $link_diff = $crawler->countLinks($new_map) - count($flat_map);
        $this->setMessage('build_sitemaps', 'Added ' . $link_diff . ' links to ' . $site_config->getLabel());
        $sitemaps++;
      } else {
        $this->stopStopWatch('build_sitemaps');
      }
      $offset++;
    }
    $this->stopStopWatch('build_sitemaps');

    if ($sitemaps < 1) {
      $this->setErrorMessage('build_sitemaps', 'Unable to find sitemap to update');
    }

    return $sitemaps;
  }

  /**
   * Parse as many links from sitemap as possible.
   * @param \AppBundle\Util\int|NULL $allowed_time
   */
  public function parseSitemap($allowed_time = 0) {
    $parse_count = 0;

    //Start stopwatch
    $this->startStopWatch('parse_sitemap');

    // Attempt to find a site config with a site map.
    $site_config = false;

    // Find oldest site config with sitemap and content types.
    $filter = ['sitemap' => ['$exists' => true], 'document_type_instances' => ['$exists' => true]];
    if ($site = $this->database->findDocuments('site_config', $filter, ['last_update_date' => 1], 1)) {
      $site_config = $site;
    }

    // Have a site config?
    if ($site_config) {
      $created = 0;
      $crawler = new ULSiteCrawler($this->database, $site_config);
      $parser = new ULParser();

      $flat_map = $crawler->flattenSitemap($site_config->getSitemap());
      $new_map = [];

      foreach ($flat_map as &$page) {
        // Not past time, and not already parsed url?
        $content_document = (isset($page['content_document_id']) && $this->database->loadContentDocument($page['content_document_id']));
        if (!$this->overAllowedTime('parse_sitemap', $allowed_time) && !$content_document) {
          if ($crawler->parseSitemapPage($page, $parser)) {
            $created++;
          }
        }
        $new_map[] = $page;
      }
      $this->stopStopWatch('parse_sitemap');

      $site_config->setSitemap($crawler->inflateSitemap($new_map));
      $site_config->setLastUpdateDate(time());
      $this->database->updateDocument($site_config);
      $this->setMessage('parse_sitemap', 'Created ' . $created . ' Content documents for ' . $site_config->getLabel());
      $parse_count += $created;
    } else {
      $this->setErrorMessage('parse_sitemap', 'Unable to find site config with sitemap and document type instances.');
    }

    return $parse_count;
  }

  private function startStopWatch($key) {

    if (!isset($this->stop_watch[$key])) {
      $this->stop_watch[$key] = ['start' => time()];
    }
    else {
      $this->stop_watch[$key]['start'] = time();
    }
  }

  private function stopStopWatch($key) {
    $this->stop_watch[$key]['stop'] = time();
  }

  private function checkStopWatch($key) {
    $now = time();
    return $now - $this->stop_watch[$key]['start'];
  }

  public function timeSpent($key) {
    return ($this->stop_watch[$key]['stop'] - $this->stop_watch[$key]['start']);
  }

  private function getMinimalTime($max_limit = 0, $item_count = 0) {
    $minimal_time = 0;

    if (!$max_limit) {
      // Set to max allowed time - 3 seconds.
      $max_limit = (ini_get('max_execution_time') - 3);
      //in case that is too short.
      if ($max_limit < 1) {
        // set to default
        $max_limit = $this->time_limit;
      }
    }

    if ($item_count < 2) {
      $minimal_time = $max_limit;
    }
    else {
      $minimal_time = floor($max_limit / $item_count);
      if ($minimal_time < 1) {
        $minimal_time = 1;
      }
    }

    return $minimal_time;
  }


  private function overAllowedTime($key, $limit = 0) {
    // Has stop watch been stopped?
    if (isset($this->stop_watch[$key]['stop'])) {
      return true;
    }

    $limit = $this->getMinimalTime($limit);

    return ($this->checkStopWatch($key) >= $limit);
  }

  private function setMessage($key, $message) {
    if (!isset($this->messages[$key])) {
      $this->messages[$key] = [];
    }
    $this->messages[$key][] = $message;
  }

  public function getMessages($key = null) {
    $messages = array();
    if ($key) {
      if (isset($this->messages[$key])) {
        $messages = $this->messages[$key];
      }
    }
    else {
      $messages = $this->messages;
    }

    return $this->formatMessages($messages, $key);
  }

  private function setErrorMessage($key, $message) {
    if (!isset($this->error_messages[$key])) {
      $this->error_messages[$key] = [];
    }

    $this->error_messages[$key][] = $message;
  }

  public function getErrorMessages($key = null) {
    $messages = array();

    if ($key) {
      if (isset($this->error_messages[$key])) {
        $messages = $this->error_messages[$key];
      }
    }
    else {
      $messages = $this->error_messages;
    }

    return $this->formatMessages($messages, $key);
  }

  private function formatMessages($messages, $key = null) {
    $return_messages = array();
    if ($messages) {
      foreach ($messages as $mkey => &$message) {
        if (is_array($message)) {
          foreach($message as $time => $text) {
            $return_messages[] = array(
              'time' => $time,
              'text' => $text,
              'key' => $key ?: $mkey,
            );
          }
        } else {
          $return_messages[] = array(
            'time' => $mkey,
            'text' => $message,
            'key' => $key ?: '',
          );
        }
      }
    }

    return $return_messages;
  }

}