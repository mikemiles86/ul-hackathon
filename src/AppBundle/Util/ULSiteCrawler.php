<?php

namespace AppBundle\Util;

use AppBundle\Document\content_document;
use Symfony\Component\DomCrawler\Crawler;
use AppBundle\Document\site_config;

class ULSiteCrawler {

  private $database;
  private $site_config;

  public function __construct(ULDatabase $database, site_config $site_config) {
    $this->database = $database;
    $this->site_config = $site_config;
  }


  public function buildSitemap($sitemap, $max_nesting = 5) {
    // Flatten the sitemap.
    $flat_map = $this->flattenSitemap($sitemap);
    // Set known links.
    $this->setKnownLinks(array_keys($flat_map));
    $new_sitemap = $flat_map;
    // Go forth and prosper the globe.
    foreach($flat_map as $site_page) {
      $new_sitemap = array_merge($new_sitemap, $this->buildSubMap($site_page, 1, $max_nesting));
    }

    // return inflated sitemap.
    return $this->inflateSitemap($new_sitemap);
  }


  private function buildSubMap($page, $nest, $max_nest) {
    // Get Raw content, accessbile from db?
    $content = null;
    $submap = array();

    if ($nest > $max_nest) {
      return $submap;
    }

    if (isset($page['content_document_id']) && $content_document = $this->database->loadContentDocument($page['content_document_id'])) {
      $content = $content_document->getRawContent();
      $nest -= 1;
    }
    // else make a curl call
    elseif (isset($page['url']) && ($raw = $this->getPageContent($page['url']))) {
      $content = $raw;
    }

    // Have content to crawl and found links?
    if ($content && ($sub_links = $this->getPageLinks($content))) {
      // Filter out non domain links.
      $site_links = $this->filterDomainLinks($sub_links, $this->site_config->getSiteDomain());
      // Add any unknown links to the sitemap.
      foreach ($site_links as $url) {
        if (!$this->isKnownLink($url)) {
          // Add to known links.
          $this->addKnownLink($url);
          $sub_page = [
            'url' => $url,
            'parent' => $page['url'],
          ];

          // Add to sitemap.
          $submap[$url] = $sub_page;
          // Go get children.
          if ($sub_submap = $this->buildSubMap($sub_page, $nest + 1, $max_nest)) {
            $submap = array_merge($submap, $sub_submap);
          }
        }
      }
    }

    return $submap;
  }

  public function flattenSitemap($sitemap) {
    $flatmap = [];

    foreach ($sitemap as $page) {
      $flatmap[$page['url']] = $page;
      if (isset($page['children'])) {
        $flatmap = array_merge($flatmap, $this->flattenSitemap($page['children']));
      }
    }

    return $flatmap;
  }

  public function inflateSitemap ($flat_map, $parent = '') {
     $sitemap = [];

    // find all items that have parent.
    foreach ($flat_map as $page) {
      if (isset($page['parent']) && ($page['parent'] == $parent)) {
        if ($children = $this->inflateSitemap($flat_map, $page['url'])) {
          $page['children'] = $children;
        }
        $sitemap[] = $page;
      }
    }

    return $sitemap;
  }


  private function isKnownLink($url) {
    return in_array($url, $this->known_links);
  }

  public function setKnownLinks($links) {
    $this->known_links = $links;
  }

  public function addKnownLink($url) {
    if (!in_array($url, $this->known_links)) {
      $this->known_links[] = $url;
    }
  }

  public function parseSitemapPage(&$page, ULParser $parser) {
    // Already have content id?
    if (isset($page['content_document_id'])) {
      return FALSE;
    }
    // Able to get page content?
    if ($raw_content = $this->getPagecontent($page['url'])) {
      // Loop through all available content types.
      foreach ($this->site_config->getDocumentTypeInstances() as $document_type) {

        // Able to parse into this type?
        if ($parsed_content = $parser->parseContentData($raw_content, $document_type)) {
          // Create a new Content Document.
          $content_document = new content_document;
          $content_document->setSite($this->site_config->getSiteConfigId());
          $content_document->setDocumentType($document_type['type_id']);
          $content_document->setUrl($page['url']);
          $content_document->setRawContent($raw_content);
          $content_document->setParsedContent($parsed_content);
          $content_document->setCreateDate(time());
          // Save the document.
          $this->database->createDocument($content_document);
          //Check if saved, by getting Id.
          if ($content_document_id = $content_document->getContentDocumentId()) {
            // Set the id to the sitemap item.
            $page['content_document_id'] = $content_document_id;
            return TRUE;
          }
        }
      }
    }

    return FALSE;
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
      $href = trim(trim($href), '/');
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
      $url = trim($url);
      // Skip empty urls.
      if (empty($url) || ($url == '/')) {
        continue;
      }
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
    if ($content['site_id']) {
      $content_document = new ULContentDocument();
      $content_document->setSite($content['site_id']);
      $content_document->setRawContent($content['raw_content']);
      $content_document->setUrl($content['url']);

      return $content_document;
    }

    return false;
  }

  public function crawlSite($max_nesting = 5, $max_discovery = 100) {

    $this->max_nesting = $max_nesting;
    $this->max_discovery = $max_discovery;
    $this->discovery_count = 0;

    $this->traverseSite($this->site->getSiteDomain());
    return $this->discovery_count;
  }

  private function traverseSite($url, $nest_level = 1) {

    // Find a content document with this url.
    if (($nest_level < $this->max_nesting) && ($this->discovery_count < $this->max_discovery)) {

      if ($page = $this->crawlPage($url,$this->site->getSiteDomain())) {
        // Page have links?
        if (isset($page['links'])) {
          // Send the links for discovery.
          foreach ($page['links'] as $link_url) {
            $this->traverseSite($link_url, $nest_level+1);
          }
        }
        // Page have raw content?
        if (isset($page['raw_content'])) {
          // Create a content document.
          $content = [
            'site_id' => $this->site->getSiteConfigId(),
            'url' => $url,
            'raw_content' => $page['raw_content'],
          ];

          if ($this->createContentDocument($content)) {
            $this->discovery_count++;
            $this->addKnownLink($url);
          }
        }
      }
    }
  }

  public function countLinks($sitemap) {
    $sitemap = $this->flattenSitemap($sitemap);
    return count($sitemap);
  }

}