<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class ULContentDocument implements ULContentDocumentInterface {

    /**
     * @MongoDB\Id
     */
    private $content_document_id;

    /**
     * @MongoDB\Field(type="string")
     */
    private $document_type;

    /**
     * @MongoDB\Field(type="string")
     */
    private $site_id;

    /**
     * @MongoDB\Field(type="string")
     */
    private $url;

    /**
     * @MongoDB\Field(type="timestamp")
     */
    private $last_updated;

    /**
     * @MongoDB\Field(type="timestamp")
     */
    private $create_date;

    /**
     * @MongoDB\Field(type="string")
     */
    private $discovery_type;

    /**
     * @MongoDB\Field(type="string")
     */
    private $raw_content;

    /**
     * @MongoDB\Field(type="collection")
     */
    private $parsed_content;

    /**
     * @MongoDB\Field(type="collection")
     */
    private $site_usage;

    /**
     * @MongoDB\Field(type="collection")
     */
    private $metadata;

    public function __get($property){

    }

    public function __set($property, $value){

    }

    /**
     * Get contentDocumentId
     *
     * @return id $contentDocumentId
     */
    public function getContentDocumentId()
    {
        return $this->content_document_id;
    }

    /**
     * Set lastUpdated
     *
     * @param timestamp $lastUpdated
     * @return $this
     */
    public function setLastUpdated($lastUpdated)
    {
        $this->last_updated = $lastUpdated;
        return $this;
    }

    /**
     * Get lastUpdated
     *
     * @return timestamp $lastUpdated
     */
    public function getLastUpdated()
    {
        return $this->last_updated;
    }

    /**
     * Set site
     *
     * @param string $site
     * @return $this
     */
    public function setSite($site)
    {
        $this->site_id = $site;
        return $this;
    }

    /**
     * Get site
     *
     * @return string $site
     */
    public function getSite()
    {
        return $this->site_id;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get url
     *
     * @return string $url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set documentType
     *
     * @param string $documentType
     * @return $this
     */
    public function setDocumentType($documentType)
    {
        $this->document_type = $documentType;
        return $this;
    }

    /**
     * Get documentType
     *
     * @return string $documentType
     */
    public function getDocumentType()
    {
        return $this->document_type;
    }

    /**
     * Set parsedContent
     *
     * @param collection $parsedContent
     * @return $this
     */
    public function setParsedContent($parsedContent)
    {
        $this->parsed_content = $parsedContent;
        return $this;
    }

    /**
     * Get parsedContent
     *
     * @return collection $parsedContent
     */
    public function getParsedContent()
    {
        return $this->parsed_content;
    }

    /**
     * Set siteId
     *
     * @param string $siteId
     * @return $this
     */
    public function setSiteId($siteId)
    {
        $this->site_id = $siteId;
        return $this;
    }

    /**
     * Get siteId
     *
     * @return string $siteId
     */
    public function getSiteId()
    {
        return $this->site_id;
    }

    /**
     * Set createDate
     *
     * @param timestamp $createDate
     * @return $this
     */
    public function setCreateDate($createDate)
    {
        $this->create_date = $createDate;
        return $this;
    }

    /**
     * Get createDate
     *
     * @return timestamp $createDate
     */
    public function getCreateDate()
    {
        return $this->create_date;
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
     * Set rawContent
     *
     * @param string $rawContent
     * @return $this
     */
    public function setRawContent($rawContent)
    {
        $this->raw_content = $rawContent;
        return $this;
    }

    /**
     * Get rawContent
     *
     * @return string $rawContent
     */
    public function getRawContent()
    {
        return $this->raw_content;
    }

    /**
     * Set siteUsage
     *
     * @param collection $siteUsage
     * @return $this
     */
    public function setSiteUsage($siteUsage)
    {
        $this->site_usage = $siteUsage;
        return $this;
    }

    /**
     * Get siteUsage
     *
     * @return collection $siteUsage
     */
    public function getSiteUsage()
    {
        return $this->site_usage;
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

    public function create() {}

    public function update() {}

    public function show() {}

}
