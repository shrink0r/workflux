<?php

namespace Workflux\Tests\Builder;

use Workflux\Builder\YamlStateMachineBuilder;
use Workflux\StateMachineInterface;
use Workflux\Tests\TestCase;

class YamlStateMachineBuilderTest extends TestCase
{
    public function testBuild()
    {
        $state_machine = (new YamlStateMachineBuilder(__DIR__.'/Fixture/statemachine.yaml'))
            ->build();

        $this->assertEquals('bar', $state_machine->getStates()->get('new')->getSetting('foo'));
        $this->assertInstanceOf(StateMachineInterface::CLASS, $state_machine);
    }
}
