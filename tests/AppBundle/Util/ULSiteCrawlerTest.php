<?php

namespace Tests\AppBundle\Util;

use AppBundle\Util\ULSiteCrawler;

class ULSiteCrawlerTest extends \PHPUnit_Framework_TestCase {

  /**
   * Test that a valid page can be retrieved.
   */
  public function testGetPageContentTrue() {
      $crawler = new ULSiteCrawler();

      $result = $crawler->getPageContent('http://www.wearegenuine.com');

      $this->assertNotEmpty($result);
  }

  /**
   * Test that a non existing page cannot be retrieved.
   */
  public function testGetPageContentFalse() {
    $crawler = new ULSiteCrawler();

    $result = $crawler->getPageContent('http://wwww.test.dev123');
    $this->assertEmpty($result);
  }

  /**
   * Test that a non-html page is not retrieved.
   */
  public function testGetPageContentNotHtml() {
    $crawler = new ULSiteCrawler();

    $result = $crawler->getPageContent('https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png');
    $this->assertEmpty($result);
  }

  /**
   * Test that links can be retrieved from a page.
   */
  public function testGetPageLinks() {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
    <head>
        <title>Hello World</title>
    </head>
    <body>
        <a href="www.google.com">google</a>
        <p>
            <a href="/some/path">Relative Path</a>
        </p>
        <div>
            <div>
                <p>
                    <ul>
                        <li>
                            <a href="http://wwww.test.dev/sub/path">Sub Path</a>
                        </li>
                    </ul>
                </p>
            </div>
        </div>
        <a href="/image.png">Image Path</a>
    </body>
</html>
HTML;

    $crawler = new ULSiteCrawler();
    $result = $crawler->getPageLinks($html);
    $this->assertNotEmpty($result);
  }

  /**
   * Test to remove links to other sites.
   */
  public function testFilterDomainLinks() {
    $domain = 'http://www.test.dev';
    $stub_links = [
      'http://www.google.com',
      'www.google.com',
      'http://www.test.dev/path/one',
      'www.test.dev/path/two',
      'google.com/mypath',
      'test.dev/path/three',
      'path/four',
      'five'
    ];

    $expected = [
      'http://www.test.dev/path/one',
      'http://www.test.dev/path/two',
      'http://www.test.dev/path/three',
      'http://www.test.dev/path/four',
      'http://www.test.dev/five',
    ];

    $crawler = new ULSiteCrawler();
    $result = $crawler->filterDomainLinks($stub_links, $domain);
    $this->assertEquals($expected, $result);
  }

  /**
   * Test to filter out already known links.
   */
  public function testFilterKnownLinks() {

    $stub_known_links = [
      'http://www.test.dev/one',
      'http://www.test.dev/path/two',
    ];

    $stub_links = [
      'http://www.test.dev/path/three',
      'http://www.test.dev/path/two',
      'http://www.test.dev/one',
      'http://www.test.dev/path/four',
    ];

    $expected = [
      'http://www.test.dev/path/three',
      'http://www.test.dev/path/four',
    ];

    $crawler = new ULSiteCrawler();
    $result = $crawler->filterKnownLinks($stub_links, $stub_known_links);
    $this->assertEquals($expected, $result);
  }

  /**
   * Test to check if able to crawl a URL.
   */
  public function testCrawlPage() {

    $stub_domain = 'https://www.wearegenuine.com';
    $stub_url = 'https://www.wearegenuine.com/careers';

    $crawler = new ULSiteCrawler();
    $crawler->addKnownLink($stub_domain);
    $result = $crawler->crawlPage($stub_url, $stub_domain);

    $this->assertArrayHasKey('raw_content', $result);
    $this->assertArrayHasKey('links', $result);
  }

}