<?php

namespace AppBundle\Util;

use Doctrine\MongoDB\Connection;

class ULDatabase implements ULDatabaseInterface {

  private $connection;

  public function __construct(Connection $connection)
  {
    $this->connection = $connection;
  }

  /**
   * Retrieves a content document from MongoDB based on Id.
   *
   * @param string $content_document_id
   *   The content document id
   *
   * @return Object
   *   Return a PHP object of the content document or boolean FALSE.
   */
  public function loadContentDocument($content_document_id){
    // connect
    $m = $this->connection;
    $m->connect();

    // select a database
    $db = $m->selectDatabase('ulhackathon');

    // select a collection (analogous to a relational database's table)
    $collection = $db->selectCollection('content_document');

    // find the content_document in the collection
    $cursor = $collection->findOne(['_id' => $content_document_id]);

    return $cursor;
  }

  /**
   * Saves a content document to the MongoDB.
   *
   * @param Object $content_document
   *   The content_document php object.
   *
   * @return bool
   *   true or false if successful.
   */
  public function saveContentDocument($content_document){

  }


  /**
   * @param string $site_id
   *   the unique id for the site
   *
   * @return Object
   *   Return PHP object of the site config document or boolean FALSE.
   */
  public function loadSiteConfig($site_id){
    // connect
    $m = $this->connection;
    $m->connect();

    // select a database
    $db = $m->selectDatabase('ulhackathon');

    // select a collection (analogous to a relational database's table)
    $collection = $db->selectCollection('site_config');

    // find the content_document in the collection
    $cursor = $collection->findOne(['_id' => $site_id]);

    return $cursor;
  }


  /**
   * Saves a site config to the MongoDB.
   *
   * @param Object $site_config
   *   The site_config php object.
   *
   * @return bool
   *   true or false if successful.
   */
  public function saveSiteConfig($site_config){

  }

}
