<?php

namespace AppBundle\Utils

use Symfony\Component\DomCrawler\Crawler;

class ContentParser {

  private $crawler;
  //private $database;

  public function __construct(Crawler $crawler) {

    $this->crawler = $crawler;
    //$this->database = $database;

  }

}