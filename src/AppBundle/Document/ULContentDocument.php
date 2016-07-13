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
    public $content_document_id;

    /**
     * @MongoDB\Field(type="timestamp")
     */
    public $last_updated;

    /**
     * @MongoDB\Field(type="string")
     */
    public $site;

    /**
     * @MongoDB\Field(type="string")
     */
    public $url;

    /**
     * @MongoDB\Field(type="string")
     */
    public $document_type;

    /**
     * @MongoDB\Field(type="string")
     */
    public $parsed_content;

    public function __get($property){

    }

    public function __set($property, $value){

    }

    public function update(){

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
        $this->site = $site;
        return $this;
    }

    /**
     * Get site
     *
     * @return string $site
     */
    public function getSite()
    {
        return $this->site;
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
     * @param string $parsedContent
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
     * @return string $parsedContent
     */
    public function getParsedContent()
    {
        return $this->parsed_content;
    }
}
