<?php

namespace AppBundle\Dummy;

use AppBundle\Util\ULSiteConfigInterface;

class ULSiteConfigDummy implements ULSiteConfigInterface {

  private $document_type_instances;

  public function __construct($document_type_instances) {
    $this->document_type_instances = $document_type_instances;
  }

  public function getDocumentTypeInstances() {
    return $this->document_type_instances;
  }

}