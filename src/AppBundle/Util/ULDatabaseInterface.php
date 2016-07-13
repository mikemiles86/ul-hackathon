<?php

namespace AppBundle\Util;

interface ULDatabaseInterface {

  /**
   * Retrieves a content document from MongoDB based on Id.
   *
   * @param string $content_document_id
   *   The content document id
   *
   * @return Object
   *   Return a PHP object of the content document or boolean FALSE.
   */
  public function loadContentDocument($content_document_id);


  /**
   * Saves a content document to the MongoDB.
   *
   * @param Object $content_document
   *   The content_document php object.
   *
   * @return bool
   *   true or false if successful.
   */
  public function saveContentDocument($content_document);


  /**
   * @param string $site_id
   *   the unique id for the site
   *
   * @return Object
   *   Return PHP object of the site config document or boolean FALSE.
   */
  public function loadSiteConfig($site_id);


  /**
   * Saves a site config to the MongoDB.
   *
   * @param Object $site_config
   *   The site_config php object.
   *
   * @return bool
   *   true or false if successful.
   */
  public function saveSiteConfig($site_config);

}