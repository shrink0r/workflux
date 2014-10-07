<?php

namespace Workflux\Tests\Parser\Xml;

use Workflux\Tests\BaseTestCase;
use Workflux\Error\Error;
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

    public function testLiteralize()
    {
        $state_machine_definition_file = dirname(__FILE__) . '/Fixture/literalize_test.xml';

        $parser = new StateMachineDefinitionParser();
        $expected = include dirname(__FILE__) . '/Fixture/literalize_test.php';

        $parsed_config = $parser->parse($state_machine_definition_file);

        $this->assertEquals($expected, $parsed_config);
    }

    public function testNonReadableClass()
    {
        $state_machine_definition_file = dirname(__FILE__) . '/Fixture/unreadable_state_machine.xml';
        $this->setExpectedException(
            Error::CLASS,
            sprintf('Unable to read fsm definition file at location: %s', $state_machine_definition_file)
        );

        $parser = new StateMachineDefinitionParser();

        $expected = include dirname(__FILE__) . '/Fixture/state_machine.php';
        $parser->parse($state_machine_definition_file);
    }
}
