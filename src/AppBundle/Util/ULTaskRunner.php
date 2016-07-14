<?php

namespace AppBundle\Util;

use AppBundle\Util\ULDatabase;
use AppBundle\Util\ULParser;

class ULTaskRunner {

  private $database;
  private $stop_watch;
  private $time_limit = 5;
  private $error_messages = array();

  public function __construct(ULDatabase $database) {
    $this->database = $database;
    return $this;
  }

  public function updateContentDocuments($allowed_time = 0) {
    $update_count = 0;
    // Start watch.
    $this->startStopWatch('update_content');

    // Loop and do as many as possible.
    while (!$this->overAllowedTime('update_content', $allowed_time)) {
      $content_document = false;
      // Find content document that has never been updated
      if ($document = $this->database->findDocuments('content_document',['last_update_date' => null], ['create_date' => 'ASC'], 1)) {
        $content_document = $document;
      // Else find oldest
      } elseif ($document = $this->database->findDocuments('content_document',[], ['last_update_date' => 'ASC'], 1)) {
        $content_document = $document;
      }

      // Have a content document && site config?
      if ($content_document && ($site_config = $this->database->loadSiteConfig($content_document[0]->getSiteId()))) {
        // Create a new Parser.
        $parser = new ULParser();
        // Update content document.
        $parser->parseContentDocument($content_document, $site_config);
        // Set update time.
        $content_document->setLastUpdated(time());
        // Save updated.
        $this->database->saveContentDocument($content_document);
        $update_count++;

      }
      // else stop the watch.
      else {
        $this->stopStopWatch('update_content');
      }
    }

    return $update_count;
  }

  /**
   * Update links in as many sitemaps as possilbe.
   * @param \AppBundle\Util\int|NULL $allowed_time
   */
  public function buildSitemaps($allowed_time = 0) {
    $build = array('sitemaps' => 0, 'links' => 0);

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

        $new_map = $crawler->inflateSitemap($new_map);
        // Save sitemap.
        $site_config->setSitemap($new_map);
        // Update last update date.
        $site_config->setLastUpdateDate(time());
        // Save site config.
        $this->database->updateDocument($site_config);
        $build['sitemaps']++;
        $build['links'] += $crawler->countLinks($new_map);
      } else {
        $this->stopStopWatch('build_sitemaps');
      }
      $offset++;
    }
    $this->stopStopWatch('build_sitemaps');

    return $build;
  }

  /**
   * Parse as many links from sitemap as possible.
   * @param \AppBundle\Util\int|NULL $allowed_time
   */
  public function parseSitemap($allowed_time = 0) {
    $response = false;

    //Start stopwatch
    $this->startStopWatch('parse_sitemap');

    // Attempt to find a site config with a site map.
    $site_config = false;

    // Find oldest site config with sitemap and content types.
    $filter = ['sitemap' => ['$exists' => true], 'document_type_instances' => ['$exists' => true]];
    if ($site = $this->database->findDocuments('site_config', $filter, ['last_update_date' => 'ASC'], 1)) {
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
        if (!$this->overAllowedTime('parse_sitemap', $allowed_time) && !isset($page['content_document_id'])) {
          if ($crawler->parseSitemapPage($page, $parser)) {
            $created++;
          }
        }
        $new_map[] = $page;
      }
      $this->stopStopWatch('parse_sitemap');

      $site_config->setSitemap($crawler->inflateSitemap($new_map));
      $this->database->updateDocument($site_config);
      $response = 'Created ' . $created . ' Content documents for ' . $site_config->getLabel();
    } else {
      $this->setErrorMessage('parse_sitemap', 'Unable to find site config with sitemap and document type instances.');
    }

    return $response;
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

  private function overAllowedTime($key, $limit = 0) {
    // Has stop watch been stopped?
    if (isset($this->stop_watch[$key]['stop'])) {
      return true;
    }

    // Limit not set?
    if (!$limit) {
      // Set to max allowed time - 3 seconds.
      $limit = (ini_get('max_execution_time') - 3);
      //in case that is too short.
      if ($limit < 1) {
        // set to default
        $limit = $this->time_limit;
      }
    }
    return ($this->checkStopWatch($key) >= $limit);
  }

  private function setErrorMessage($key, $message) {
    if (!isset($this->error_messages[$key])) {
      $this->error_messages[$key] = [];
    }

    $this->error_messages[$key][] = $message;
  }

  public function getErrorMessage($key) {
    $messages = false;
    if (isset($this->error_messages[$key])) {
      $messages = $this->error_messages[$key];
    }
    return $messages;
  }

}