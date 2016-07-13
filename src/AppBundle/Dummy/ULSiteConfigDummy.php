<?php

namespace AppBundle\Dummy;

use AppBundle\Document\ULSiteConfig;

class ULSiteConfigDummy extends ULSiteConfig {

  private $document_type_instances;
  private $site_domain;
  private $_id;

  public function __construct($document_type_instances = []) {
    $this->document_type_instances = $document_type_instances;
  }

  public function getDocumentTypeInstances() {
    return $this->document_type_instances;
  }

}