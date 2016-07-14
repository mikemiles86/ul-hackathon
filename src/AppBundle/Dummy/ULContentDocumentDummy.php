<?php

namespace AppBundle\Dummy;

use AppBundle\Document\content_document;
use AppBundle\Document\site_config;

class ULContentDocumentDummy extends content_document {

  private $url;
  private $document_type;
  private $last_updated;
  private $site;
  private $raw_content;
  private $parsed_content;

  public function __construct($url, site_config $site, $document_type = null, $last_updated = null) {
    $this->url = $url;
    $this->site = $site;
    if ($document_type) {
      $this->document_type = $document_type;
    }
    if ($last_updated) {
      $this->last_updated = $last_updated;
    }
  }

  public function __get($variable) {
    return $this->$variable;
  }

  public function __set($variable, $value) {
    $this->variable = $value;
  }

  public function getSite() {
    return $this->site;
  }

  public function update() {
    return TRUE;
  }

}