<?php

namespace Workflux\Tests\StateMachine;

use Workflux\Tests\BaseTestCase;
use Workflux\StateMachine\IStateMachine;
use Workflux\StateMachine\StateMachine;
use Workflux\StateMachine\StateMachineBuilder;
use Workflux\State\IState;
use Workflux\State\State;
use Workflux\Transition\Transition;
use Workflux\Tests\Fixture\GenericSubject;
use Workflux\Renderer\DotGraphRenderer;

class StateMachineBuilderTest extends BaseTestCase
{
    public function testCreateStateMachine()
    {
        $states = [
            new State('editing', IState::TYPE_INITIAL),
            new State('approval'),
            new State('published'),
            new State('deleted', IState::TYPE_FINAL)
        ];

        $transitons = [
            new Transition('promote', [ 'editing' ], 'approval'),
            new Transition('promote', [ 'approval' ], 'published'),
            new Transition('delete', [ 'editing', 'approval', 'published' ], 'deleted'),
            new Transition('demote', [ 'published', 'approval' ], 'editing')
        ];

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName('test_machine')
            ->addStates($states)
            ->addTransitions($transitons)
            ->createStateMachine();

        $this->assertEquals('test_machine', $state_machine->getName());
        $this->assertEquals($transitons[0], $state_machine->getTransition('editing', 'promote'));
        $this->assertEquals($states[0], $state_machine->getState('editing'));
        $this->assertEquals($states[1], $state_machine->getState('approval'));
    }
}
