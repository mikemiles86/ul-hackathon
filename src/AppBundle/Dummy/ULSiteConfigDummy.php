<?php

namespace AppBundle\Dummy;

use AppBundle\Document\site_config;

class ULSiteConfigDummy extends site_config {

  private $document_type_instances = [];
  private $site_config_id = 'wearegenuine';
  private $site_domain = 'https://www.wearegenuine.com';


  public function __construct($document_type_instances = [], $domain = null, $site_config_id = null) {
    $this->document_type_instances = $document_type_instances;
    if ($domain) {
      $this->setSiteDomain($domain);
    }
    if ($site_config_id) {
      $this->site_config_id = $site_config_id;
    }
  }

  public function getSiteDomain() {
    return 'https://www.wearegenuine.com/careers';
  }

  public function getSiteConfigId() {
    return 'wearegenuine';
  }

  public function getDocumentTypeInstances() {
    return $this->document_type_instances;
  }

}