<?php

namespace Workflux\Tests\StateMachine;

use Workflux\Error\Error;
use Workflux\Tests\BaseTestCase;
use Workflux\StateMachine\IStateMachine;
use Workflux\StateMachine\StateMachine;
use Workflux\State\IState;
use Workflux\State\State;
use Workflux\Transition\Transition;
use Workflux\Guard\CallbackGuard;
use Workflux\Guard\ExpressionGuard;
use Workflux\Tests\Fixture\GenericSubject;
use Workflux\IStatefulSubject;

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
                'promote' => [ new Transition('state1', 'state2') ]
            ]
        ];

        $state_machine = new StateMachine('test_machine', $states, $transitions);

        $this->assertEquals('test_machine', $state_machine->getName());
        $this->assertEquals($states['state1'], $state_machine->getState('state1'));
        $this->assertEquals($states['state2'], $state_machine->getState('state2'));
        $this->assertContains(
            $transitions['state1']['promote'][0],
            $state_machine->getTransitions('state1', 'promote')
        );
    }

    public function testExecute()
    {
        $states = [
            'state1' => new State('state1', IState::TYPE_INITIAL),
            'state2' => new State('state2', IState::TYPE_FINAL)
        ];
        $transitions = [
            'state1' => [
                'promote' => [ new Transition('state1', 'state2') ]
            ]
        ];

        $subject = new GenericSubject('test_machine', 'state1');
        $state_machine = new StateMachine('test_machine', $states, $transitions);
        $target_state = $state_machine->execute($subject, 'promote');

        $this->assertEquals('state2', $target_state->getName());
    }

    public function testExecuteSimpleDecisionFalse()
    {
        $subject = new GenericSubject('test_machine', 'new');

        $states = [
            'new' => new State('new', IState::TYPE_INITIAL),
            'transcoding' => new State('transcoding'),
            'ready' => new State('ready', IState::TYPE_FINAL)
        ];

        $transitions = [
            'new' => [
                'promote' => [
                    new Transition(
                        'new',
                        'transcoding',
                        new ExpressionGuard([ 'expression' => 'params.transcoding_required' ])
                    ),
                    new Transition(
                        'new',
                        'ready',
                        new ExpressionGuard([ 'expression' => 'not params.transcoding_required' ])
                    )
                ]
            ]
        ];

        $state_machine = new StateMachine('test_machine', $states, $transitions);

        $subject->getExecutionState()->setParameter('transcoding_required', false);
        $target_state = $state_machine->execute($subject, 'promote');

        $this->assertEquals('ready', $target_state->getName());
    }

    public function testExecuteSimpleDecisionTrue()
    {
        $subject = new GenericSubject('test_machine', 'new');

        $states = [
            'new' => new State('new', IState::TYPE_INITIAL),
            'transcoding' => new State('transcoding'),
            'ready' => new State('ready', IState::TYPE_FINAL)
        ];

        $transitions = [
            'new' => [
                'promote' => [
                    new Transition(
                        'new',
                        'transcoding',
                        new ExpressionGuard([ 'expression' => 'params.transcoding_required' ])
                    ),
                    new Transition(
                        'new',
                        'ready',
                        new ExpressionGuard([ 'expression' => 'not params.transcoding_required' ])
                    )
                ]
            ]
        ];

        $state_machine = new StateMachine('test_machine', $states, $transitions);

        $subject->getExecutionState()->setParameter('transcoding_required', true);
        $target_state = $state_machine->execute($subject, 'promote');

        $this->assertEquals('transcoding', $target_state->getName());
    }

    public function testInvalidSubject()
    {
        $this->setExpectedException(
            Error::CLASS,
            'Unable to resolve the given state-name "erpen_derp" to an existing state.'
        );

        $states = [
            'state1' => new State('state1', IState::TYPE_INITIAL),
            'state2' => new State('state2', IState::TYPE_FINAL)
        ];
        $transitions = [
            'state1' => [
                'promote' => [ new Transition('state1', 'state2') ]
            ]
        ];

        $subject = new GenericSubject('test_machine', 'erpen_derp');
        $state_machine = new StateMachine('test_machine', $states, $transitions);

        $state_machine->execute($subject, 'promote');
    }

    public function testInvalidTransition()
    {
        $this->setExpectedException(
            Error::CLASS,
            'No transitions available for event "erpen_derp" at state "state1".'
        );

        $states = [
            'state1' => new State('state1', IState::TYPE_INITIAL),
            'state2' => new State('state2', IState::TYPE_FINAL)
        ];
        $transitions = [
            'state1' => [
                'promote' => [ new Transition('state1', 'state2') ]
            ]
        ];

        $subject = new GenericSubject('test_machine', 'state1');
        $state_machine = new StateMachine('test_machine', $states, $transitions);

        $state_machine->execute($subject, 'erpen_derp');
    }

    public function testGetNonExistingTransitions()
    {
        $this->setExpectedException(
            Error::CLASS,
            'No transitions available at state "non_existant".'
        );

        $states = [
            'state1' => new State('state1', IState::TYPE_INITIAL),
            'state2' => new State('state2', IState::TYPE_FINAL)
        ];
        $transitions = [
            'state1' => [
                'promote' => [ new Transition('state1', 'state2') ]
            ]
        ];

        $subject = new GenericSubject('test_machine', 'state1');
        $state_machine = new StateMachine('test_machine', $states, $transitions);
        $state_machine->getTransitions('non_existant');
    }

    public function testRejectedTransition()
    {
        $this->setExpectedException(
            Error::CLASS,
            'Transition for event "promote" at state "state1" was rejected.'
        );

        $rejecting_guard = new CallbackGuard(
            function (IStatefulSubject $subject) {
                return false;
            }
        );

        $states = [
            'state1' => new State('state1', IState::TYPE_INITIAL),
            'state2' => new State('state2', IState::TYPE_FINAL)
        ];
        $transitions = [
            'state1' => [
                'promote' => [ new Transition('state1', 'state2', $rejecting_guard) ]
            ]
        ];

        $subject = new GenericSubject('test_machine', 'state1');
        $state_machine = new StateMachine('test_machine', $states, $transitions);

        $state_machine->execute($subject, 'promote');
    }

    public function testTooManyAcceptedGuards()
    {
        $this->setExpectedException(
            Error::CLASS,
            'Only one transition is allowed to be active at a time.'
        );

        $accepting_guard = new CallbackGuard(
            function (IStatefulSubject $subject) {
                return true;
            }
        );

        $states = [
            'state1' => new State('state1', IState::TYPE_INITIAL),
            'state2' => new State('state2'),
            'state2' => new State('state3', IState::TYPE_FINAL)
        ];
        $transitions = [
            'state1' => [
                'promote' => [
                    new Transition('state1', 'state2', $accepting_guard),
                    new Transition('state1', 'state3', $accepting_guard)
                ]
            ]
        ];

        $subject = new GenericSubject('test_machine', 'state1');
        $state_machine = new StateMachine('test_machine', $states, $transitions);

        $state_machine->execute($subject, 'promote');
    }
}
