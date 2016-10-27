<?php

namespace Workflux\Tests\Builder;

use Workflux\Builder\YamlStateMachineBuilder;
use Workflux\Param\Input;
use Workflux\StateMachineInterface;
use Workflux\Tests\TestCase;

class YamlStateMachineBuilderTest extends TestCase
{
    public function testBuild()
    {
        $state_machine = (new YamlStateMachineBuilder(__DIR__.'/Fixture/statemachine.yaml'))
            ->build();

        $rejected_transition = $state_machine->getStateTransitions()->get('transcoding')
            ->filter(function ($transition) {
                return $transition->getTo() === 'rejected';
            })->first();

        $this->assertInstanceOf(StateMachineInterface::CLASS, $state_machine);
        $this->assertEquals('bar', $state_machine->getStates()->get('new')->getSetting('foo'));
        $this->assertTrue($rejected_transition->getSetting('more_stuff'));
        $this->assertEquals(
            'ready',
            $state_machine->execute(
                new Input([ 'transcoding_required' => false ]),
                'new'
            )->getCurrentState()
        );
    }
}
