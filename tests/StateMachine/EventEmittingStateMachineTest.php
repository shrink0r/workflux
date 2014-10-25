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
    const FSM_NAME = 'test_machine';

    const S1 = 'state_one';

    const S2 = 'state_two';

    const S3 = 'state_three';

    const S4 = 'state_four';

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

        $subject = new GenericSubject(self::FSM_NAME, null);
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

        $subject = new GenericSubject(self::FSM_NAME, null);
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

        $subject = new GenericSubject(self::FSM_NAME, self::S1);
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

        $subject = new GenericSubject(self::FSM_NAME, self::S3);
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

        $subject = new GenericSubject(self::FSM_NAME);
        $state_machine->execute($subject, 'promote');

        $this->assertEquals([ self::S1, self::S2, self::S3 ], $entered_states);

        $state_machine->execute($subject, 'promote');
        $this->assertEquals([ self::S1, self::S2, self::S3, self::S4 ], $entered_states);
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

        $subject = new GenericSubject(self::FSM_NAME, null);
        $state_machine->execute($subject, 'promote');

        $this->assertEquals([ self::S1, self::S2 ], $exited_states);
    }

    public function testEventListenOnce()
    {
        $state_machine = $this->buildStateMachine();

        $entered_states = [];
        $state_machine->once(
            EventEmittingStateMachine::ON_STATE_ENTERED,
            function (
                StateMachineInterface $state_machine,
                StatefulSubjectInterface $subject,
                StateInterface $entered_state
            ) use (&$entered_states) {
                $entered_states[] = $entered_state->getName();
            }
        );

        $subject = new GenericSubject(self::FSM_NAME);
        $state_machine->execute($subject, 'promote');

        $this->assertEquals([ self::S1 ], $entered_states);
    }

    public function testRemoveEventListener()
    {
        $state_machine = $this->buildStateMachine();

        $entered_states = [];
        $callback = function (
            StateMachineInterface $state_machine,
            StatefulSubjectInterface $subject,
            StateInterface $entered_state
        ) use (&$entered_states) {
            $entered_states[] = $entered_state->getName();
        };

        $state_machine->on(EventEmittingStateMachine::ON_STATE_ENTERED, $callback);
        $state_machine->removeListener(EventEmittingStateMachine::ON_STATE_ENTERED, $callback);

        $subject = new GenericSubject(self::FSM_NAME);
        $state_machine->execute($subject, 'promote');

        $this->assertEquals([], $entered_states);
    }

    public function testRemoveAllEventListeners()
    {
        $state_machine = $this->buildStateMachine();

        $entered_states = [];
        $callback = function (
            StateMachineInterface $state_machine,
            StatefulSubjectInterface $subject,
            StateInterface $entered_state
        ) use (&$entered_states) {
            $entered_states[] = $entered_state->getName();
        };

        $state_machine->on(EventEmittingStateMachine::ON_STATE_ENTERED, $callback);
        $state_machine->removeAllListeners(EventEmittingStateMachine::ON_STATE_ENTERED);

        $subject = new GenericSubject(self::FSM_NAME);
        $state_machine->execute($subject, 'promote');

        $this->assertEquals([], $entered_states);
    }

    public function testRemoveAllListeners()
    {
        $state_machine = $this->buildStateMachine();

        $entered_states = [];
        $callback = function (
            StateMachineInterface $state_machine,
            StatefulSubjectInterface $subject,
            StateInterface $entered_state
        ) use (&$entered_states) {
            $entered_states[] = $entered_state->getName();
        };

        $state_machine->on(EventEmittingStateMachine::ON_STATE_ENTERED, $callback);
        $state_machine->removeAllListeners();

        $subject = new GenericSubject(self::FSM_NAME);
        $state_machine->execute($subject, 'promote');

        $this->assertEquals([], $entered_states);
    }

    public function testEmit()
    {
        $state_machine = $this->buildStateMachine();
        $subject = new GenericSubject(self::FSM_NAME);

        $entered_states = [];
        $callback = function (
            StateMachineInterface $state_machine,
            StatefulSubjectInterface $subject,
            StateInterface $entered_state
        ) use (&$entered_states) {
            $entered_states[] = $entered_state->getName();
        };

        $state_machine->on(EventEmittingStateMachine::ON_STATE_ENTERED, $callback);
        $state_machine->emit(
            EventEmittingStateMachine::ON_STATE_ENTERED,
            [ $state_machine, $subject, $state_machine->getState(self::S1) ]
        );

        $this->assertEquals([ self::S1 ], $entered_states);
    }

    public function testListenersGetter()
    {
        $state_machine = $this->buildStateMachine();
        $subject = new GenericSubject(self::FSM_NAME);

        $entered_states = [];
        $callback = function (
            StateMachineInterface $state_machine,
            StatefulSubjectInterface $subject,
            StateInterface $entered_state
        ) use (&$entered_states) {
            $entered_states[] = $entered_state->getName();
        };

        $state_machine->on(EventEmittingStateMachine::ON_STATE_ENTERED, $callback);
        $listeners = $state_machine->listeners(EventEmittingStateMachine::ON_STATE_ENTERED);

        $this->assertCount(1, $listeners);
        $this->assertEquals($callback, $listeners[0]);
    }

    public function testGuardInvalidEvent()
    {
        $this->setExpectedException(
            Error::CLASS,
            'Trying to register non supported event "on_erpen_derp".' .
            ' Supported are: workflux.state_machine.execution_started, workflux.state_machine.state_entered,' .
            ' workflux.state_machine.state_exited, workflux.state_machine.execution_suspended,' .
            ' workflux.state_machine.execution_resumed, workflux.state_machine.execution_finished'
        );

        $state_machine = $this->buildStateMachine();
        $subject = new GenericSubject(self::FSM_NAME);

        $entered_states = [];
        $callback = function (
            StateMachineInterface $state_machine,
            StatefulSubjectInterface $subject,
            StateInterface $entered_state
        ) use (&$entered_states) {
            $entered_states[] = $entered_state->getName();
        };

        $state_machine->on(EventEmittingStateMachine::ON_STATE_ENTERED, $callback);
        $state_machine->emit(
            'on_erpen_derp',
            [ $state_machine, $subject, $state_machine->getState(self::S1) ]
        );
    }

    protected function buildStateMachine()
    {
        $states = [
            self::S1 => new State(self::S1, StateInterface::TYPE_INITIAL),
            self::S2 => new State(self::S2),
            self::S3 => new State(self::S3),
            self::S4 => new State(self::S4, StateInterface::TYPE_FINAL)
        ];
        $transitions = [
            self::S1 => [
                'promote' => [ new Transition(self::S1, self::S2) ]
            ],
            self::S2 => [
                StateMachine::SEQ_TRANSITIONS_KEY => [ new Transition(self::S2, self::S3) ]
            ],
            self::S3 => [
                'promote' => [ new Transition(self::S3, self::S4) ]
            ]
        ];

        return new EventEmittingStateMachine(self::FSM_NAME, $states, $transitions);
    }
}
