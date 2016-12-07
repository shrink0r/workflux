<?php

namespace Workflux\Tests\Builder;

use Shrink0r\PhpSchema\FactoryInterface;
use Workflux\Builder\StateMachineSchema;
use Workflux\Tests\TestCase;

final class StateMachineSchemaTest extends TestCase
{
    public function testGetName()
    {
        $schema = new StateMachineSchema;
        $this->assertEquals('statemachine', $schema->getName());
    }

    public function testGetType()
    {
        $schema = new StateMachineSchema;
        $this->assertEquals('assoc', $schema->getType());
    }

    public function testGetCustomTypes()
    {
        $schema = new StateMachineSchema;
        $this->assertEquals([ 'transition' ], array_keys($schema->getCustomTypes()));
    }

    public function testGetProperties()
    {
        $schema = new StateMachineSchema;
        $expected_keys = [ 'class', 'name', 'states' ];
        foreach (array_keys($schema->getProperties()) as $key) {
            $this->assertContains($key, $expected_keys);
        }
    }

    public function testGetFactory()
    {
        $schema = new StateMachineSchema;
        $this->assertInstanceOf(FactoryInterface::CLASS, $schema->getFactory());
    }
}
