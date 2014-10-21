<?php

namespace Workflux\Tests\StateMachine;

use Workflux\Error\Error;
use Workflux\State\State;
use Workflux\State\StateInterface;
use Workflux\StatefulSubjectInterface;
use Workflux\StateMachine\EventEmittingStateMachine;
use Workflux\StateMachine\StateMachine;
use Workflux\StateMachine\StateMachineInterface;
use Workflux\Tests\BaseTestCase;
use Workflux\Tests\Fixture\GenericSubject;
use Workflux\Transition\Transition;

class EventEmittingStateMachineTest extends BaseTestCase
{
    public function testExecutionStartedEvent()
    {
        $state_machine = $this->buildStateMachine();

        $execution_was_started = false;
        $state_machine->on(
            EventEmittingStateMachine::ON_EXECUTION_STARTED,
            function (
                StateMachineInterface $state_machine,
                StatefulSubjectInterface $subject,
                StateInterface $entered_state
            ) use (&$execution_was_started) {
                $execution_was_started = true;
            }
        );

        $subject = new GenericSubject('test_machine', null);
        $state_machine->execute($subject, 'promote');

        $this->assertTrue($execution_was_started);
    }

    public function testExecutionSuspendedEvent()
    {
        $state_machine = $this->buildStateMachine();

        $execution_was_suspended = false;
        $state_machine->on(
            EventEmittingStateMachine::ON_EXECUTION_SUSPENDED,
            function (
                StateMachineInterface $state_machine,
                StatefulSubjectInterface $subject,
                StateInterface $entered_state
            ) use (&$execution_was_suspended) {
                $execution_was_suspended = true;
            }
        );

        $subject = new GenericSubject('test_machine', null);
        $state_machine->execute($subject, 'promote');

        $this->assertTrue($execution_was_suspended);
    }

    public function testExecutionResumedEvent()
    {
        $state_machine = $this->buildStateMachine();

        $execution_was_resumed = false;
        $state_machine->on(
            EventEmittingStateMachine::ON_EXECUTION_RESUMED,
            function (
                StateMachineInterface $state_machine,
                StatefulSubjectInterface $subject,
                StateInterface $entered_state
            ) use (&$execution_was_resumed) {
                $execution_was_resumed = true;
            }
        );

        $subject = new GenericSubject('test_machine', 'state1');
        $state_machine->execute($subject, 'promote');

        $this->assertTrue($execution_was_resumed);
    }

    public function testExecutionFinishedEvent()
    {
        $state_machine = $this->buildStateMachine();

        $execution_was_finished = false;
        $state_machine->on(
            EventEmittingStateMachine::ON_EXECUTION_FINISHED,
            function (
                StateMachineInterface $state_machine,
                StatefulSubjectInterface $subject,
                StateInterface $entered_state
            ) use (&$execution_was_finished) {
                $execution_was_finished = true;
            }
        );

        $subject = new GenericSubject('test_machine', 'state3');
        $state_machine->execute($subject, 'promote');

        $this->assertTrue($execution_was_finished);
    }

    public function testStateEnteredEvent()
    {
        $state_machine = $this->buildStateMachine();

        $entered_states = [];
        $state_machine->on(
            EventEmittingStateMachine::ON_STATE_ENTERED,
            function (
                StateMachineInterface $state_machine,
                StatefulSubjectInterface $subject,
                StateInterface $entered_state
            ) use (&$entered_states) {
                $entered_states[] = $entered_state->getName();
            }
        );

        $subject = new GenericSubject('test_machine', null);
        $state_machine->execute($subject, 'promote');

        $this->assertEquals([ 'state1', 'state2', 'state3' ], $entered_states);
    }

    public function testStateExited()
    {
        $state_machine = $this->buildStateMachine();

        $exited_states = [];
        $state_machine->on(
            EventEmittingStateMachine::ON_STATE_EXITED,
            function (
                StateMachineInterface $state_machine,
                StatefulSubjectInterface $subject,
                StateInterface $exited_state
            ) use (&$exited_states) {
                $exited_states[] = $exited_state->getName();
            }
        );

        $subject = new GenericSubject('test_machine', null);
        $state_machine->execute($subject, 'promote');

        $this->assertEquals([ 'state1', 'state2' ], $exited_states);
    }

    protected function buildStateMachine()
    {
        $states = [
            'state1' => new State('state1', StateInterface::TYPE_INITIAL),
            'state2' => new State('state2'),
            'state3' => new State('state3'),
            'state4' => new State('state4', StateInterface::TYPE_FINAL)
        ];
        $transitions = [
            'state1' => [
                'promote' => [ new Transition('state1', 'state2') ]
            ],
            'state2' => [
                StateMachine::SEQ_TRANSITIONS_KEY => [ new Transition('state2', 'state3') ]
            ],
            'state3' => [
                'promote' => [ new Transition('state3', 'state4') ]
            ]
        ];

        return new EventEmittingStateMachine('test_machine', $states, $transitions);
    }
}
