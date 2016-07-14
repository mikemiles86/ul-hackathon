<?php

namespace AppBundle\Util;

use AppBundle\Util\ULDatabase;
use AppBundle\Util\ULParser;

class ULTaskRunner {

  private $database;
  private $stop_watch;
  private $time_limit = 5;

  public function __construct(ULDatabase $database) {
    $this->database = $database;
    return $this;
  }

  public function updateContentDocuments(int $allowed_time = null) {
    $update_count = 0;
    // Start watch.
    $this->startStopWatch('update_content');

    // Loop and do as many as possible.
    while (!$this->overAllowedTime('update_content', $allowed_time)) {
      $content_document = false;
      // Find a Content Document that has never been updated.
      if ($document = $this->database->findContentDocument(['last_updated' => ''],['last_updated' => 'DESC'],1)) {
        $content_document = $document;
      }
      // Find older content document.
      elseif ($document = $this->database->findContentDocument([], ['last_updated' => 'DESC'], 1)) {
        $content_document = $document;
      }

      // Have a content document && site config?
      if ($content_document && ($site_config = $this->database->loadSiteConfig($content_document->getSiteId))) {
        // Create a new Parser.
        $parser = new ULParser();
        // Update content document.
        $parser->parseContentDocument($content_document, $site_config);
        // Set update time.
        $content_document->setLastUpdated(time());
        // Save updated.
        if ($this->database->saveContentDocument($content_document)) {
          $update_count++;
        }
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
  public function buildSitemaps(int $allowed_time = null) {

    // Get oldest updated site_config.

  }

  /**
   * Parse as many links from sitemap as possible.
   * @param \AppBundle\Util\int|NULL $allowed_time
   */
  private function parseSitemap(int $allowed_time = null) {

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
    return time() - $this->stop_watch[$key]['start'];
  }

  private function timeSpent($key) {
    return $this->stop_watch[$key]['stop'] - $this->stop_watch[$key]['start'];
  }

  private function overAllowedTime($key, $limit = 0) {
    // Has stop watch been stopped?
    if (isset($this->stop_watch[$key]['stop'])) {
      return true;
    }

    // Limit not set?
    if (!$limit) {
      // Set to max allowed time - 3 seconds.
      $limit = (ini_get('max_execution_time') * 60) - 3;
      //in case that is too short.
      if ($limit < 1) {
        // set to default
        $limit = $this->time_limit;
      }
    }
    return (($this->checkStopWatch($key) * 60) >= $limit);
  }

}