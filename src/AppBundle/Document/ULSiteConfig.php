<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class ULSiteConfig implements ULSiteConfigInterface {

  /**
   * @MongoDB\Id
   */
  private $site_config_id;

  public function getDocumentTypeInstances(){

  }


    /**
     * Get siteConfigId
     *
     * @return id $siteConfigId
     */
    public function getSiteConfigId()
    {
        return $this->site_config_id;
    }
}
