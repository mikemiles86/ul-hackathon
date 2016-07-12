<?php

namespace AppBundle\Utils

use Symfony\Component\DomCrawler\Crawler;

class ULParser {

  private $database;
  private $max_age = 86400;

  public function __construct(ULDatabaseInterface $database) {

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

    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $html = curl_exec($ch);
    curl_close($ch);

    return $html;
  }

  private function parseContentData($html, $document_type) {
    $parsed_data = array();

    $crawler = new Crawler($html);

    foreach ($document_type->field_mappings as $field) {
      $field_content = $crawler->filter($field->selector)->each(function (Crawler $node, $i) {
        return $node->text();
      });

      if (!empty($field_content)) {
        $parsed_data[] = [
          'field' => $field->machine_name,
          'selector' => $field->selector,
          'data' => $this->sanitizeContent($field_content),
        ];
      }
    }


    return empty($parsed_data) ? null:$parsed_data;
  }

  private function contentChanged($content_a, $content_b) {
    return md5($content_a) == md5($content_b);
  }

  private function updateMetaData(&$content_document) {
    // Create a 'metadata' document type.
    $metadata_type = (object)[
      ['machine_name' => 'keywords', 'selector' => 'meta [name|=Keywords]'],
      ['machine_name' => 'description', 'selector' => 'meta [name|=Description'],
    ];


    if ($metadata = $this->parseContentData($content_document->raw_content, $metadata_type)) {
      // Append to existing meta data.
    }

  }

  private function sanitizeContent($content) {
    return strip_tags($content);
  }

}