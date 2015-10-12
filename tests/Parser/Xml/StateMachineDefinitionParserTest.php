<?php

namespace Workflux\Tests\Parser\Xml;

use Workflux\Tests\BaseTestCase;
use Workflux\Error\Error;
use Workflux\Parser\Xml\StateMachineDefinitionParser;
use DOMException;

class StateMachineDefinitionParserTest extends BaseTestCase
{
    public function testParse()
    {
        $state_machine_definition_file = dirname(__FILE__) . '/Fixture/state_machine.xml';

        $parser = new StateMachineDefinitionParser();
        $parsed_definition = $parser->parse($state_machine_definition_file);

        $expected = include dirname(__FILE__) . '/Fixture/state_machine.php';

        $this->assertEquals($expected, $parsed_definition);
    }

    public function testLiteralize()
    {
        $state_machine_definition_file = dirname(__FILE__) . '/Fixture/literalize_test.xml';

        $parser = new StateMachineDefinitionParser();
        $parsed_definition = $parser->parse($state_machine_definition_file);

        $expected = include dirname(__FILE__) . '/Fixture/literalize_test.php';

        $this->assertEquals($expected, $parsed_definition);
    }

    public function testNonReadableClass()
    {
        $state_machine_definition_file = dirname(__FILE__) . '/Fixture/unreadable_state_machine.xml';
        $this->setExpectedException(
            Error::CLASS,
            sprintf('Unable to read fsm definition file at location: %s', $state_machine_definition_file)
        );

        $parser = new StateMachineDefinitionParser();
        $parser->parse($state_machine_definition_file);
    }

    public function testInvalidXmlDefinition()
    {
        $state_machine_definition_file = dirname(__FILE__) . '/Fixture/invalid_state_machine.xml';
        $this->setExpectedException(Error::CLASS);
        $this->setExpectedException(DOMException::CLASS);

        $parser = new StateMachineDefinitionParser();
        $parser->parse($state_machine_definition_file);
    }

    public function testBrokenXmlDefinition()
    {
        $this->setExpectedException(DOMException::CLASS);

        $state_machine_definition_file = dirname(__FILE__) . '/Fixture/broken_state_machine.xml';

        $parser = new StateMachineDefinitionParser();
        $parser->parse($state_machine_definition_file);
    }
}
