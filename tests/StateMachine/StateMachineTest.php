<?php

namespace Workflux\Tests\StateMachine;

use Workflux\Tests\BaseTestCase;
use Workflux\StateMachine\IStateMachine;
use Workflux\StateMachine\StateMachine;
use Workflux\State\IState;
use Workflux\State\State;
use Workflux\Transition\Transition;
use Workflux\Tests\Fixture\GenericSubject;

class StateMachineTest extends BaseTestCase
{
    public function testGetters()
    {
        $states = [
            'state1' => new State('state1', IState::TYPE_INITIAL),
            'state2' => new State('state2', IState::TYPE_FINAL)
        ];
        $transitions = [
            'state1' => [
                'promote' => new Transition('promote', [ 'state1' ], 'state2')
            ]
        ];

        $state_machine = new StateMachine('test_machine', $states, $transitions);

        $this->assertEquals('test_machine', $state_machine->getName());
        $this->assertEquals($transitions['state1']['promote'], $state_machine->getTransition('state1', 'promote'));
        $this->assertEquals($states['state1'], $state_machine->getState('state1'));
        $this->assertEquals($states['state2'], $state_machine->getState('state2'));
    }

    public function testExecute()
    {
        $states = [
            'state1' => new State('state1', IState::TYPE_INITIAL),
            'state2' => new State('state2', IState::TYPE_FINAL)
        ];
        $transitions = [
            'state1' => [
                'promote' => new Transition('promote', [ 'state1' ], 'state2')
            ]
        ];

        $subject = new GenericSubject('test_machine', 'state1');
        $state_machine = new StateMachine('test_machine', $states, $transitions);
        $target_state = $state_machine->execute($subject, 'promote');

        $this->assertEquals('state2', $target_state->getName());
    }
}
