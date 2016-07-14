<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class content_document_type {

    /**
     * @MongoDB\Id
     */
    private $content_document_type_id;

    /**
     * @MongoDB\Field(type="string")
     */
    private $label;

    /**
     * @MongoDB\Field(type="string")
     */
    private $description;

    /**
     * @MongoDB\Field(type="collection")
     */
    private $fields;

    /**
     * @MongoDB\Field(type="collection")
     */
    private $markup_types;

    /**
     * Get contentDocumentTypeId
     *
     * @return id $contentDocumentTypeId
     */
    public function getContentDocumentTypeId()
    {
        return $this->content_document_type_id;
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
     * Set description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set fields
     *
     * @param collection $fields
     * @return $this
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * Get fields
     *
     * @return collection $fields
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Set markupTypes
     *
     * @param collection $markupTypes
     * @return $this
     */
    public function setMarkupTypes($markupTypes)
    {
        $this->markup_types = $markupTypes;
        return $this;
    }

    /**
     * Get markupTypes
     *
     * @return collection $markupTypes
     */
    public function getMarkupTypes()
    {
        return $this->markup_types;
    }
}
