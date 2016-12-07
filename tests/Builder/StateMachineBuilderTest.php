<?php

namespace Workflux\Tests\Builder;

use Workflux\Builder\StateMachineBuilder;
use Workflux\Param\Settings;
use Workflux\StateMachine;
use Workflux\StateMachineInterface;
use Workflux\State\FinalState;
use Workflux\State\InitialState;
use Workflux\State\InteractiveState;
use Workflux\State\State;
use Workflux\Tests\Builder\Fixture\NonImplementedStateMachine;
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
                $this->createState('state1', InteractiveState::CLASS),
                $this->createState('state2'),
                $this->createState('final', FinalState::CLASS)
            ])
            ->addTransition(new Transition('initial', 'state1', new Settings))
            ->addTransitions([
                new Transition('state1', 'state2', new Settings),
                new Transition('state2', 'final', new Settings)
            ])
            ->build();

        $this->assertInstanceOf(StateMachineInterface::CLASS, $state_machine);
        $this->assertEquals('video-transcoding', $state_machine->getName());
    }

    /**
     * @expectedException Workflux\Error\MissingImplementation
     */
    public function testMissingInterface()
    {
        new StateMachineBuilder(NonImplementedStateMachine::CLASS);
    }

    /**
     * @expectedException Workflux\Error\MissingImplementation
     */
    public function testNonExistantClass()
    {
        new StateMachineBuilder('FooBarMachine');
    }

    /**
     * @expectedException Workflux\Error\UnknownState
     */
    public function testUnknownFromState()
    {
        (new StateMachineBuilder(StateMachine::CLASS))
            ->addStateMachineName('video-transcoding')
            ->addState($this->createState('initial', InitialState::CLASS))
            ->addState($this->createState('state1'))
            ->addState($this->createState('final', FinalState::CLASS))
            ->addTransition(new Transition('start', 'state1'));
    }

    /**
     * @expectedException Workflux\Error\UnknownState
     */
    public function testUnknownToState()
    {
        (new StateMachineBuilder(StateMachine::CLASS))
            ->addStateMachineName('video-transcoding')
            ->addState($this->createState('initial', InitialState::CLASS))
            ->addState($this->createState('state1'))
            ->addState($this->createState('final', FinalState::CLASS))
            ->addTransition(new Transition('state1', 'state2'));
    }

    /**
     * @expectedException Workflux\Error\InvalidStructure
     */
    public function testDuplicateTransition()
    {
        (new StateMachineBuilder(StateMachine::CLASS))
            ->addStateMachineName('video-transcoding')
            ->addState($this->createState('initial', InitialState::CLASS))
            ->addState($this->createState('state1'))
            ->addState($this->createState('final', FinalState::CLASS))
            ->addTransition(new Transition('initial', 'state1'))
            ->addTransition(new Transition('initial', 'state1'));
    }
}
