<?php

namespace Workflux\Tests\Parser\Xml;

use Workflux\Tests\BaseTestCase;
use Workflux\Error\Error;
use Workflux\Parser\Xml\OptionsXpathParser;
use Workflux\Parser\Xml\Xpath;
use DOMException;
use DOMDocument;

class OptionsXpathParserTest extends BaseTestCase
{
    public function testParse()
    {
        $xpath = $this->buildXpath();
        $parser = new OptionsXpathParser($xpath);
        $first_guard_element = $xpath->query('//state_machine//initial//event//transition//guard')->item(0);

        $expected = [
            'expression' => 'params.transcoding_required',
            'options_list' => [ 23, 5 ]
        ];

        $this->assertEquals($expected, $parser->parse($first_guard_element));
    }

    public function testInvalidContextElement()
    {
        $this->expectException(
            Error::CLASS,
            'Invalid "options_context" argument passed to Workflux\Parser\Xml\OptionsXpathParser::parse.' .
            ' Only instances of DOMElement supported.'
        );

        $parser = new OptionsXpathParser($this->buildXpath());
        $parser->parse('foobar');
    }

    protected function buildXpath()
    {
        $state_machine_definition_file = dirname(__FILE__) . '/Fixture/state_machine.xml';

        $dom_document = new DOMDocument('1.0', 'UTF-8');
        $dom_document->load($state_machine_definition_file);

        return new Xpath($dom_document, 'wf');
    }
}
