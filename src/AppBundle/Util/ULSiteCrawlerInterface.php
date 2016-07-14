<?php

namespace AppBundle\Util;

use AppBundle\Document\site_config;

interface ULSiteCrawlerInterface {

  public function __construct(site_config $site);

  public function getPageContent($url);

  public function getPageLinks($html);

  public function filterDomainLinks($links, $domain);

  public function crawlPage($url, $domain);

  public function crawlSite($max_nesting, $max_discovery);

}