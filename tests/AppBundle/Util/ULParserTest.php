<?php

namespace Tests\AppBundle\Util;

use AppBundle\Util\ULParser;

class ULParserTest extends \PHPUnit_Framework_TestCase {


  public function testFetchContent() {
    $parser = new ULParser();

    $result = $parser->fetchContent('http://www.google.com');
    $this->assertNotEmpty($result);
  }

  public function testSanitizeContentString() {
    $parser = new ULParser();

    $result = $parser->sanitizeContent('<strong>sanitize</strong>','field','document');
    $this->assertEquals('sanitize', $result);
  }

  public function testSanitizeContentArray() {
    $parser = new ULParser();

    $test_data = [
      'raw' => ['<b>one</b>','<b>two</b>','<b>three</b>'],
      'clean' => ['one','two','three']
    ];

    $result = $parser->sanitizeContent($test_data['raw'], 'foo', 'bar');
    $this->assertEquals($test_data['clean'],$result);
  }

  public function testParseContentDataBasic() {

    $parser = new ULParser();

    $html = <<<'HTML'
<!DOCTYPE html>
<html>
    <body>
        <p class="message">Hello World!</p>
        <p>Hello Crawler!</p>
    </body>
</html>
HTML;

    $field = ['machine_name' => 'body', 'selector' => 'body > p.message'];


    $document_type = (object)[
      'type_id' => 'test',
      'field_mappings' => [(object)$field],
    ];

    $expected = [(object)[
      'field' => $field['machine_name'],
      'selector' => $field['selector'],
      'data' => 'Hello World!',
    ]];

    $result = $parser->parseContentData($html, $document_type);
    $this->assertEquals($expected, $result);

  }

  public function testParseContentDataComplex() {

    $parser = new ULParser();

    $html = <<<'HTML'
<!DOCTYPE html>
<html>
    <body>
        <h1>Title</h1>
        <p class="main-content">
            This is content
        </p>
        <a class="author" href="author" />
        <a class="author" href="co author" />
    </body>
</html>
HTML;

    $document_type = (object)[
      'type_id' => 'complex',
      'field_mappings' => [
        (object)[
          'machine_name' => 'title',
          'selector' => 'h1',
        ],
        (object)[
          'machine_name' => 'body',
          'selector' => 'body > p.main-content',
        ],
        (object)[
          'machine_name' => 'author',
          'selector' => (object)[
            'selector' => 'body > a.author',
            'extract' => array('href'),
            'multiple' => true,
          ]
        ]
      ]
    ];

    $expected = [
      (object)[
        'field' => 'title',
        'selector' => 'h1',
        'data' => 'Title'
      ],
      (object)[
        'field' => 'body',
        'selector' => 'body > p.main-content',
        'data' => 'This is content',
      ],
      (object)[
        'field' => 'author',
        'selector' => 'body > a.author',
        'data' => array('author','co author'),
      ]
    ];

    $result = $parser->parseContentData($html, $document_type);
    $this->assertEquals($expected, $result);
  }

  public function testGetMetaData() {
    $parser = new ULParser();

    $html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="keywords" content="hello,world" />
        <meta name="description" content="hello world" />
    </head>
    <body>
        <p>Hello World!</p>
    </body>
</html>
HTML;

    $expected = [
      (object)[
        'field' => 'keywords',
        'selector' => '//meta[@name="keywords"]',
        'data' => array('hello','world'),
      ],
      (object)[
        'field' => 'description',
        'selector' => '//meta[@name="description"]',
        'data' => 'hello world',
      ],
      (object)[
        'field' => 'language',
        'selector' => 'html',
        'data' => 'en',
      ]
    ];

    $result = $parser->getMetaData($html);
    $this->assertEquals($expected, $result);

  }
}

