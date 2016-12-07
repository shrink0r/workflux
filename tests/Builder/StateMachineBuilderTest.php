<?php

namespace Workflux\Tests\Builder;

use Workflux\Builder\StateMachineBuilder;
use Workflux\Param\Settings;
use Workflux\StateMachine;
use Workflux\StateMachineInterface;
use Workflux\State\InteractiveState;
use Workflux\State\FinalState;
use Workflux\State\InitialState;
use Workflux\State\State;
use Workflux\Tests\TestCase;
use Workflux\Transition\Transition;

class StateMachineBuilderTest extends TestCase
{
    public function testBuild()
    {
        $state_machine = (new StateMachineBuilder(StateMachine::CLASS))
            ->addStateMachineName('video-transcoding')
            ->addState($this->createState('initial', InitialState::CLASS))
            ->addStates([
                $this->createState('foobar', InteractiveState::CLASS),
                $this->createState('bar'),
                $this->createState('final', FinalState::CLASS)
            ])
            ->addTransition(new Transition('initial', 'foobar', new Settings))
            ->addTransitions([
                new Transition('foobar', 'bar', new Settings),
                new Transition('bar', 'final', new Settings)
            ])
            ->build();

        $this->assertInstanceOf(StateMachineInterface::CLASS, $state_machine);
    }
}
