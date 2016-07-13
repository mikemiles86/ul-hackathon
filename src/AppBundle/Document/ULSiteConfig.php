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

    /**
     * @MongoDB\Field(type="string")
     */
    private $label;

    /**
     * @MongoDB\Field(type="string")
     */
    private $site_domain;

    /**
     * @MongoDB\Field(type="timestamp")
     */
    private $last_update_date;

    /**
     * @MongoDB\Field(type="string")
     */
    private $discovery_type;

    /**
     * @MongoDB\Field(type="boolean")
     */
    private $lock_status;

    /**
     * @MongoDB\Field(type="collection")
     */
    private $metadata;

    /**
     * @MongoDB\Field(type="collection")
     */
    private $document_type_instances;

    /**
     * Get siteConfigId
     *
     * @return id $siteConfigId
     */
    public function getSiteConfigId()
    {
        return $this->site_config_id;
    }

    /**
     * Set label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Get label
     *
     * @return string $label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set siteDomain
     *
     * @param string $siteDomain
     * @return $this
     */
    public function setSiteDomain($siteDomain)
    {
        $this->site_domain = $siteDomain;
        return $this;
    }

    /**
     * Get siteDomain
     *
     * @return string $siteDomain
     */
    public function getSiteDomain()
    {
        return $this->site_domain;
    }

    /**
     * Set lastUpdateDate
     *
     * @param timestamp $lastUpdateDate
     * @return $this
     */
    public function setLastUpdateDate($lastUpdateDate)
    {
        $this->last_update_date = $lastUpdateDate;
        return $this;
    }

    /**
     * Get lastUpdateDate
     *
     * @return timestamp $lastUpdateDate
     */
    public function getLastUpdateDate()
    {
        return $this->last_update_date;
    }

    /**
     * Set discoveryType
     *
     * @param string $discoveryType
     * @return $this
     */
    public function setDiscoveryType($discoveryType)
    {
        $this->discovery_type = $discoveryType;
        return $this;
    }

    /**
     * Get discoveryType
     *
     * @return string $discoveryType
     */
    public function getDiscoveryType()
    {
        return $this->discovery_type;
    }

    /**
     * Set lockStatus
     *
     * @param boolean $lockStatus
     * @return $this
     */
    public function setLockStatus($lockStatus)
    {
        $this->lock_status = $lockStatus;
        return $this;
    }

    /**
     * Get lockStatus
     *
     * @return boolean $lockStatus
     */
    public function getLockStatus()
    {
        return $this->lock_status;
    }

    /**
     * Set metadata
     *
     * @param collection $metadata
     * @return $this
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * Get metadata
     *
     * @return collection $metadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Set documentTypeInstances
     *
     * @param collection $documentTypeInstances
     * @return $this
     */
    public function setDocumentTypeInstances($documentTypeInstances)
    {
        $this->document_type_instances = $documentTypeInstances;
        return $this;
    }

    /**
     * Set documentTypeInstances
     *
     * @return collection $documentTypeInstances
     */
    public function getDocumentTypeInstances(){
      return $this->document_type_instances;
    }
}
