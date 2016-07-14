<?php

namespace AppBundle\Util;

use AppBundle\Document\content_document;
use Symfony\Component\DomCrawler\Crawler;
use AppBundle\Document\site_config;

class ULSiteCrawler implements ULSiteCrawlerInterface {

  private $known_links = array();
  private $site;

  public function __construct(site_config $site) {
    $this->site = $site;
  }

  public function addKnownLink($url) {
    if (!in_array($url, $this->known_links)) {
      $this->known_links[] = $url;
    }
  }
  /**
   * Retrieve the HTML content of a page.
   *
   * @param string $url
   *   The URL to traverse.
   *
   * @return mixed|null
   *   Null or hmtl string.
   */
  public function getPageContent($url) {
    $html = null;

    // Use cURL to fetch content.
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
    $content = curl_exec($ch);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $response_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    // success response and document is of type text/html?
    if (($response_code == 200) && (substr($content_type,0,9) == 'text/html')) {
      $html = $content;
    }

    return $html;
  }

  /**
   * Get all urls from link elements on a page.
   *
   * @param string $html
   *   String of HTML
   *
   * @return array
   *   Array of found URLs
   */
  public function getPageLinks($html) {
    $links = array();

    $crawler = new Crawler();
    $crawler->addHTMLContent($html);

    $links = $crawler->filter('a')->each(function(Crawler $node, $i){
      $href = $node->extract(array('href'));
      $href = array_pop($href);
      // remove leading and trailing slashes.
      $href = trim($href, '/');
      return $href;
    });

    $links = array_unique($links);

    return $links;
  }

  /**
   * Filter links that are from a different domain.
   *
   * @param array $links
   *   Array of url links
   *
   * @param string $domain
   *   the domain to test against.
   *
   * @return array
   *   Array of links minus the ones from different sites.
   */
  public function filterDomainLinks($links, $domain) {
    $filtered = array();
    // Break into parts.
    $parsed_domain = parse_url($domain);

    $domain_subdomain = '';
    if (substr_count($parsed_domain['host'],'.') > 1) {
      $domain_subdomain = substr($parsed_domain['host'],0,strpos($parsed_domain['host'],'.')) . '.';
    }

    $domain_scheme = '';
    if (isset($parsed_domain['scheme']) && !empty($parsed_domain['scheme'])) {
      $domain_scheme = $parsed_domain['scheme'] . '://';
    }

    // Loop through each of the link urls.
    foreach ($links as $url) {
      $clean_url = '';
      // break into parts.
      $parsed_url = parse_url($url);

      // Does host match?
      if (isset($parsed_url['host'])) {
        if ($parsed_url['host'] == $parsed_domain['host']) {
          $clean_url = $url;
        }
      }
      else if (isset($parsed_url['path'])) {
        // explode on slashes.
        if (strpos($parsed_url['path'], '/')) {
          $parsed_path = explode('/', $parsed_url['path']);
        } else {
          $parsed_path = array($parsed_url['path']);
        }
        // Check that first element matches host.
        if ($parsed_path[0] == $parsed_domain['host']) {
          $clean_url = $domain_scheme . $url;
        }
        // First part have a . in it?
        elseif (strpos($parsed_path[0],'.')) {
          // match inside host?
          if (strpos($parsed_domain['host'], $parsed_path[0])) {
            // Get host prefix
            $clean_url = $domain_scheme . $domain_subdomain . $url;
          }
          // Assume a sub-path.
        } elseif (!strpos($parsed_path[0],':')) {
          $clean_url = $domain_scheme . $parsed_domain['host'] . '/' . $url;
        }
      }

      if (!empty($clean_url)) {
        $filtered[] = $clean_url;
      }
    }

    return $filtered;
  }

  /**
   * Filter out links that are already known.
   *
   * @param array $links
   *   Array of links.
   * @param array $known_links
   *   Array of known links.
   *
   * @return array
   *   Array of links minus the known ones.
   */
  public function filterKnownLinks($links, $known_links) {
    $filtered = array();

    foreach ($links as $url) {
      if (!in_array($url, $known_links)) {
        $filtered[] = $url;
      }
    }

    return $filtered;
  }

  public function crawlPage($url, $domain) {
    $page = array();

    // URL match domain looking for?
    if (count($this->filterDomainLinks([$url], $domain)) == 1) {
      // Able to get valid content?
      if ($html = $this->getPageContent($url)) {
        $this->addKnownLink($url);
        $page['raw_content'] = $html;
        // Find links on the page?
        if ($links = $this->getPageLinks($html)) {
          // Filter out links.
          $links = $this->filterDomainLinks($links, $domain);
          $links = $this->filterKnownLinks($links, $this->known_links);
          if (count($links) > 0) {
            $page['links'] = $links;
          }
        }
      }
    }

    return $page;
  }

  public function createContentDocument($content) {
    $content_document = new content_document();
    $content_document->setSite($content['site_id']);
    //$content->setRawContent($content['raw_content']);
   // $content->setCreateDate(time());
    $content_document->setUrl($content['url']);

    return $content_document;

  }

}