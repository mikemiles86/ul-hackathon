<?php

namespace AppBundle\Util;

interface ULSiteCrawlerInterface {

  public function getPageContent($url);

  public function getPageLinks($html);

  public function filterDomainLinks($links, $domain);

  public function crawlPage($url, $domain);
}