<?php

namespace AppBundle\Util;

use AppBundle\Document\content_document;
use AppBundle\Document\content_document_type;
use AppBundle\Document\site_config;
use Doctrine\MongoDB\Connection;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

class ULDatabase implements ULDatabaseInterface {

  private $connection;

  private $manager;

  public function __construct(Connection $connection, ManagerRegistry $manager)
  {
    $this->connection = $connection;
    $this->manager = $manager;
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
   * @param content_document $content_document
   *   The content_document php object.
   *
   * @return bool
   *   true or false if successful.
   */
  public function saveContentDocument($content_document){
    // connect
    $m = $this->connection;
    $m->connect();

    // select a database
    $db = $m->selectDatabase('ulhackathon');

    // select a collection (analogous to a relational database's table)
    $collection = $db->selectCollection('content_document');

    //@TODO: Save.
  }

  /**
   * @param string $site_config_id
   *   the unique id for the site
   *
   * @return site_config
   *   Return PHP object of the site config document or boolean FALSE.
   */
  public function loadSiteConfig($site_config_id){
    // connect
    $m = $this->connection;
    $m->connect();

    // select a database
    $db = $m->selectDatabase('ulhackathon');

    // select a collection (analogous to a relational database's table)
    $collection = $db->selectCollection('site_config');

    // find the content_document in the collection
    $cursor = $collection->findOne(['_id' => $site_config_id]);

    return $cursor;
  }


  /**
   * Saves a site config to the MongoDB.
   *
   * @param site_config $site_config
   *   The site_config php object.
   *
   * @return bool
   *   true or false if successful.
   */
  public function saveSiteConfig($site_config){
    // connect
    $m = $this->connection;
    $m->connect();

    // select a database
    $db = $m->selectDatabase('ulhackathon');

    // select a collection (analogous to a relational database's table)
    $collection = $db->selectCollection('site_config');

    //@TODO: Save.
  }

  /**
   * @param string $content_document_type_id
   *   the unique id for the site
   *
   * @return content_document_type
   *   Return PHP object of the site config document or boolean FALSE.
   */
  public function loadContentDocumentType($content_document_type_id){
    // connect
    $m = $this->connection;
    $m->connect();

    // select a database
    $db = $m->selectDatabase('ulhackathon');

    // select a collection (analogous to a relational database's table)
    $collection = $db->selectCollection('content_document_type');

    // find the content_document in the collection
    $cursor = $collection->findOne(['_id' => $content_document_type_id]);

    return $cursor;
  }

  /**
   * Saves a site config to the MongoDB.
   *
   * @param content_document_type $content_document_type
   *   The site_config php object.
   *
   * @return bool
   *   true or false if successful.
   */
  public function saveContentDocumentType($content_document_type){
    // connect
    $m = $this->connection;
    $m->connect();

    // select a database
    $db = $m->selectDatabase('ulhackathon');

    // select a collection (analogous to a relational database's table)
    $collection = $db->selectCollection('content_document_type');

    //@TODO: Save.
  }

  /**
   * @param string $document_type
   *   the type of document
   *
   * @param string $id
   *   the unique id for the document
   *
   * @return Object
   *   Return PHP object of the site config document or boolean FALSE.
   */
  public function loadDocument($document_type, $id){
    $document = $this->manager
      ->getRepository('AppBundle:' . $document_type)
      ->find($id);

    return $document;
  }

  /**
   * Saves a site config to the MongoDB.
   *
   * @param Object $document
   *   The site_config php object.
   *
   * @return bool
   *   true or false if successful.
   */
  public function saveDocument($document){
  }
}
