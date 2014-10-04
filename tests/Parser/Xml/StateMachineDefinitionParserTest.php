<?php

namespace Workflux\Tests\Parser\Xml;

use Workflux\Tests\BaseTestCase;
use Workflux\Parser\Xml\StateMachineDefinitionParser;

class StateMachineDefinitionParserTest extends BaseTestCase
{
    public function testParse()
    {
        $state_machine_definition_file = dirname(__FILE__) . '/Fixture/state_machine.xml';
        $parser = new StateMachineDefinitionParser();

        $expected = include dirname(__FILE__) . '/Fixture/state_machine.php';
        $parsed_definition = $parser->parse($state_machine_definition_file);

        $this->assertEquals($expected, $parsed_definition);
    }
}
