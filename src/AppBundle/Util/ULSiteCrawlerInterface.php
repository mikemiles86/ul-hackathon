<?php

namespace AppBundle\Util;

use AppBundle\Document\ULSiteConfig;

interface ULSiteCrawlerInterface {

  public function __construct(ULSiteConfig $site);

  public function getPageContent($url);

  public function getPageLinks($html);

  public function filterDomainLinks($links, $domain);

  public function crawlPage($url, $domain);

  public function crawlSite($max_nesting, $max_discovery);

}