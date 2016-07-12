<?php

namespace AppBundle\Utils

use EightPoints\Bundle\GuzzleBundle\GuzzleBundle;
use Symfony\Component\DomCrawler\Crawler;
use AppBundle\Utils\ULDatabaseInterface;

class ULParser implements ULParserInterface {

  private $crawler;
  private $database;
  private $guzzle;
  private $max_age = 86400;

  public function __construct(Crawler $crawler, GuzzleBundle $guzzle, ULDatabaseInterface $database) {

    $this->crawler = $crawler;
    $this->guzzle = $guzzle
    $this->database = $database;
    return $this;
  }

  public function parseContentDocument(ULContentDocumentInterface $content_document) {
    // Check to see if needs to be updated.
    if ($this->needsUpdate($content_document->last_updated)) {
      // Able to get the site information?
      if ($site == $content_document->getSite()) {
        // Able to get raw content?
        if ($raw = $this->fetchContent($content_document->url)) {
          $parsed = FALSE;
          // Able to get the document type?
          if ($document_type = $content_document->document_type) {
            // parse based on the type found.
            $parsed = $this->parseContentData($raw, $document_type);
          }
          // No document type found, loop through them.
          else {
            foreach ($site->getDocumentTypeInstances() as $document_type) {
              if ($parsed = $this->parseContentData($raw, $document_type)) {
                // update the document type.
                $content_document->document_type = $document_type->type_id;
                // break out of the loop.
              }
            }
          }

          // Have parsed and different from previous version?
          if ($parsed && $this->contentChanged($parsed, $content_document->parsed_content)) {
            // Update parsed.
            $content_document->parsed_content = $parsed;
            // Update raw.
            $content_document->raw_content = $raw;
            // Update the metadata.
            $this->updateMetaData($content_document);
            // Update the content document.
            $content_document->update();
          }

        }

      }
    }
  }

  private function needsUpdate($datetime) {
    $update = FALSE;

    // If no datetime set or older then max allowed age.
    if (!$datetime || ((time() - $datetime) > $this->max_age)) {
      $update = TRUE;
    }

    return $update;
  }

  private function fetchContent($url) {
    $html = null;

    return $html;
  }

  private function parseContentData($html, $document_type) {
    $parsed_data = null;

    return $parsed_data;
  }

  private function contentChanged($content_a, $content_b) {
    return md5($content_a) == md5($content_b);
  }

  private function updateMetaData(&$content_document) {

  }

}