<?php

namespace AppBundle\Util;

use Symfony\Component\DomCrawler\Crawler;

class ULParser {

  private $max_age = 86400;
  private $match_threshold = 75;

  /**
   * Parse and update a content document.
   *
   * @param \AppBundle\Util\ULContentDocumentInterface $content_document
   */
  public function parseContentDocument(ULContentDocumentInterface $content_document) {
    // Check to see if needs to be updated.
    if (!isset($content_document->last_updated) || ($this->needsUpdate($content_document->last_updated))) {
      // Able to get the site information?
      if ($site = $content_document->getSite()) {
        // Able to get raw content?
        if ($raw = $this->fetchContent($content_document->url)) {

          $parsed = FALSE;
          // Able to get the document type?
          if (isset($content_document->document_type) && $content_document->document_type) {
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
          if ($parsed && (!isset($content_document->parsed_content) || $this->contentChanged($parsed, $content_document->parsed_content))) {
            // Update parsed.
            $content_document->parsed_content = $parsed;
            // Update raw.
            $content_document->raw_content = $raw;
            // Update the metadata.
            $this->updateMetaData($content_document);
            // Update the content document.
            if ($content_document->update()) {
              return TRUE;
            }
          }
        }
      }
    }
    return FALSE;
  }

  /**
   * Boolean check if datetime is older then allowed max age.
   *
   * @param string $datetime
   *   datatime string.
   *
   * @return bool
   *  boolean true or false.
   */
  private function needsUpdate($datetime) {
    $update = FALSE;

    // If no datetime set or older then max allowed age.
    if (!$datetime || ((time() - $datetime) > $this->max_age)) {
      $update = TRUE;
    }

    return $update;
  }

  /**
   * Retrieve HTML output from a URL.
   *
   * @param string $url
   *   Url string
   *
   * @return mixed|null
   *   HTML string or null.
   */
  public function fetchContent($url) {
    $html = null;
    // Use cURL to fetch content.
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $html = curl_exec($ch);
    curl_close($ch);

    return $html;
  }

  /**
   * Parse a content string into a content type.
   * @param $html
   * @param $document_type
   * @return array|null
   */
  public function parseContentData($html, $document_type) {
    $parsed_data = array();

    $crawler = new Crawler();
    $crawler->addHtmlContent($html);

    foreach ($document_type->field_mappings as $field) {
      $field_content = $this->getSelectorValue($crawler, $field->selector);

      if (!empty($field_content)) {
        $parsed_data[] = (object)[
          'field' => $field->machine_name,
          'selector' => $this->getSelectorString($field->selector),
          'data' => $this->sanitizeContent($field_content, $field->machine_name, $document_type->type_id),
        ];
      }
    }
    // Have not matched enough fields?
    if (!$this->metThreshold(count($parsed_data), count($document_type->field_mappings))) {
      $parsed_data = false;
    }

    return empty($parsed_data) ? null:$parsed_data;
  }

  /**
   * Check to see if data meets needed threshold of a match.
   *
   * @param $check
   *   The count to check.
   * @param $max
   *   The max number to meet.
   *
   * @return bool
   *   Boolean true or false is threshold is met or exceeded.
   */
  private function metThreshold($check, $max) {
    $met = false;
    // Is check greater then zero?
    if ($check > 0) {
      // See if percentage is equal or greater then match threshold.
      if (intval(($check/$max)*100) >= $this->match_threshold) {
        $met = true;
      }
    }

    return $met;
  }


  private function getSelectorString($selector) {
    $selector_string = '';

    if (is_string($selector)) {
      $selector_string = $selector;
    }
    elseif (isset($selector->selector)) {
      $selector_string = $selector->selector;
    }

    return $selector_string;
  }
  /**
   * Retrieve the value from HTML Crawler that matches passed selector.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   DOM Crawler
   * @param string|object $selector
   *  Selector string or selector object.
   *
   * @return array|null|string
   *   Return found data.
   */
  private function getSelectorValue(Crawler $crawler, $selector) {

    $value = null;
    // Empty selector? (root document)
    if (empty($selector)) {
      $value = $crawler->text();
    // Just a basic selector string?
    } elseif (is_string($selector)) {
      if ($crawler->filter($selector)->count() > 0) {
        $value = $crawler->filter($selector)->text();
      }
    }
    // else using a complex selector.
    else {
      // Selector type defined? if not assume basic CSS selector.
      $selector_type = isset($selector->type) ? $selector->type : 'css';

      if (isset($selector->selector)) {
        switch ($selector_type) {
          case 'xpath':
            $element = $crawler->filterXPath($selector->selector);
            break;
          case 'css':
          default:
            $element = $crawler->filter($selector->selector);
            break;
        }
      } else {
        $element = $crawler;
      }

      // Selector accepting multiple values?
      if (isset($selector->multiple) && $selector->multiple && ($element->count() > 0)) {
        // Crawl each instance of selector
        $value = $element->each(function (Crawler $node, $i) use ($selector) {
            // Selector looking for specific value/attribute to extract?
            if (isset($selector->extract)) {
              $value = $node->extract($selector->extract);
              if (is_array($value) && (count($value) == 1)) {
                $value = array_pop($value);
              }
              return $value;
            }
            // else just return selector text value.
            else {
              return $node->text();
            }
        });
      }
      // Single selector, looking for specific value/attribute?
      elseif (isset($selector->extract) && ($element->count() > 0)) {
        // Extract passed value or attribute.
        $value = $element->extract($selector->extract);
      }
      // Else retrieve element text value.
      elseif ($element->count() > 0){
        $value = $element->text();
      }
    }

    if (is_array($value) && (count($value) == 1)) {
      $value = array_pop($value);
    }

    return $value;
  }

  /**
   * Compare two objects and see if they arethe same or different.
   *
   * @param mixed $content_a
   *   Object a
   * @param $content_b
   *   Object b
   *
   * @return bool
   *   Boolean TRUE if not matched, Boolean FALSE is match.
   */
  public function contentChanged($content_a, $content_b) {
    // Compare hashes of the two objects.
    return (md5($content_a) != md5($content_b));
  }

  /**
   * Retrieve and update metadata values for a content document.
   *
   * @param Object $content_document
   *   Content Document object.
   */
  private function updateMetaData(&$content_document) {
    // Get the original metadata value
    $content_metadata = isset($content_document->metadata) ? $content_document->metadata : array();


    // Get metadata content.
    if ($metadata = $this->getMetaData($content_document->raw_content)) {
      // Loop through each returned metadata values.
      foreach ($metadata as $data) {
        // Different actions based on field.
        switch ($data->field) {
          // Meta keywords.
          case 'keywords':
            // Merge with existing array or set new array.
            if (!isset($content_metadata['keywords'])) {
              $content_metadata['keywords'] = $data->data;
            }
            else {
              $content_metadata['keywords'] = array_merge($content_metadata['keywords'], $data->data);
            }
          break;
          // Default acton, just overwrite existing value.
          default:
            $content_metadata[$data->field] = $data->data;
        }
      }
      // Override the document metadata.
      $content_document->metadata = $content_metadata;
    }
  }

  /**
   * Sanitized retrieved content.
   *
   * @param string|array $content
   *   String or array of content to snitize.
   *
   * @param string $field_name
   *   The name of the field content is for.
   *
   * @param $document_type
   *   The name of the document type.
   *
   * @return string
   *   The sanitized string.
   */
  public function sanitizeContent($content, $field_name, $document_type) {
    $sanitized = '';

    if (is_array($content)) {
      foreach ($content as &$sub_content) {
        $sub_content = $this->sanitizeContent($sub_content, $field_name, $document_type);
      }
      $sanitized = $content;
    }
    else {
      $sanitized = trim(strip_tags($content));
    }

    return $sanitized;
  }

  public function getMetaData($html) {
    $metadata = null;
    if ($data = $this->parseContentData($html, $this->getMetaDataType())) {
      foreach ($data as $field) {
        switch($field->field) {
          case 'keywords':
            $field->data = explode(',', $field->data);
            break;
        }
      }
      $metadata = $data;
    }

    return $metadata;
  }

  /**
   * Create a mock DocumentType for metadata.
   *
   * @return object
   */
  private function getMetaDataType() {

    $metadata_type = [];

    $metadata_type[] = (object)[
      'machine_name' => 'keywords',
      'selector' => (object)[
        'type' => 'xpath',
        'selector' => '//meta[@name="keywords"]',
        'extract' => array('content'),
      ]
    ];
    $metadata_type[] = (object)[
      'machine_name' => 'description',
      'selector' => (object)[
        'type' => 'xpath',
        'selector' => '//meta[@name="description"]',
        'extract' => array('content'),
      ]
    ];
    $metadata_type[] = (object)[
      'machine_name' => 'language',
      'selector' => (object)[
        'type' => 'xpath',
        'extract' => array('lang'),
        'selector' => 'html',
      ]
    ];

    $metadata_type = (object)[
      'type_id' => 'metadata',
      'field_mappings' => $metadata_type,
    ];

    return $metadata_type;
  }

}