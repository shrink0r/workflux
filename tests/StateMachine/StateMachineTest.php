<?php

namespace Workflux\Tests\StateMachine;

use Workflux\Error\Error;
use Workflux\Tests\BaseTestCase;
use Workflux\StateMachine\StateMachineInterface;
use Workflux\StateMachine\StateMachine;
use Workflux\State\StateInterface;
use Workflux\State\State;
use Workflux\Transition\Transition;
use Workflux\Guard\CallbackGuard;
use Workflux\Guard\ExpressionGuard;
use Workflux\Tests\Fixture\GenericSubject;
use Workflux\StatefulSubjectInterface;

class StateMachineTest extends BaseTestCase
{
    public function testGetters()
    {
        $states = [
            'state1' => new State('state1', StateInterface::TYPE_INITIAL),
            'state2' => new State('state2', StateInterface::TYPE_FINAL)
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
        $this->assertEquals([ $states['state2'] ], $state_machine->getFinalStates());
        $this->assertEquals([ $states['state1'] ], $state_machine->getEventStates());
        $this->assertContains(
            $transitions['state1']['promote'][0],
            $state_machine->getTransitions('state1', 'promote')
        );
    }

    public function testExecute()
    {
        $states = [
            'edit' => new State('edit', StateInterface::TYPE_INITIAL),
            'approval' => new State('approval'),
            'published' => new State('published', StateInterface::TYPE_FINAL)
        ];
        $transitions = [
            'edit' => [
                'promote' => [ new Transition('edit', 'approval') ]
            ],
            'approval' => [
                '_sequential' => [ new Transition('approval', 'published') ]
            ]
        ];

        $subject = new GenericSubject('test_machine', 'edit');
        $state_machine = new StateMachine('test_machine', $states, $transitions);
        $target_state = $state_machine->execute($subject, 'promote');

        $this->assertEquals('test_machine', $subject->getExecutionContext()->getStateMachineName());
        $this->assertEquals('published', $target_state->getName());
    }

    public function testExecuteSimpleDecisionFalse()
    {
        $subject = new GenericSubject('test_machine', 'new');

        $states = [
            'new' => new State('new', StateInterface::TYPE_INITIAL),
            'transcoding' => new State('transcoding'),
            'ready' => new State('ready', StateInterface::TYPE_FINAL)
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
            ],
            'transcoding' => [
                'promote' => [ new Transition('transcoding', 'ready') ]
            ]
        ];

        $state_machine = new StateMachine('test_machine', $states, $transitions);

        $subject->getExecutionContext()->setParameter('transcoding_required', false);
        $target_state = $state_machine->execute($subject, 'promote');

        $this->assertEquals('ready', $target_state->getName());
    }

    public function testExecuteSimpleDecisionTrue()
    {
        $subject = new GenericSubject('test_machine', 'new');

        $states = [
            'new' => new State('new', StateInterface::TYPE_INITIAL),
            'transcoding' => new State('transcoding'),
            'ready' => new State('ready', StateInterface::TYPE_FINAL)
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
            ],
            'transcoding' => [
                'promote' => [ new Transition('transcoding', 'ready') ]
            ]
        ];

        $state_machine = new StateMachine('test_machine', $states, $transitions);

        $subject->getExecutionContext()->setParameter('transcoding_required', true);
        $target_state = $state_machine->execute($subject, 'promote');

        $this->assertEquals('transcoding', $target_state->getName());
    }

    public function testInvalidResumeState()
    {
        $this->setExpectedException(
            Error::CLASS,
            'Current execution is pointing to an invalid state approval.' .
            ' The state machine execution must be started and resume by entering an event state.'
        );

        $states = [
            'edit' => new State('edit', StateInterface::TYPE_INITIAL),
            'approval' => new State('approval'),
            'published' => new State('published', StateInterface::TYPE_FINAL)
        ];
        $transitions = [
            'edit' => [
                'promote' => [ new Transition('edit', 'approval') ]
            ],
            'approval' => [
                '_sequential' => [ new Transition('approval', 'published') ]
            ]
        ];

        $subject = new GenericSubject('test_machine', 'approval');
        $state_machine = new StateMachine('test_machine', $states, $transitions);
        $state_machine->execute($subject, 'promote');
    }

    public function testInvalidSubject()
    {
        $this->setExpectedException(
            Error::CLASS,
            'Unable to resolve the given state-name "erpen_derp" to an existing state.'
        );

        $states = [
            'state1' => new State('state1', StateInterface::TYPE_INITIAL),
            'state2' => new State('state2', StateInterface::TYPE_FINAL)
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
            'state1' => new State('state1', StateInterface::TYPE_INITIAL),
            'state2' => new State('state2', StateInterface::TYPE_FINAL)
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
            'state1' => new State('state1', StateInterface::TYPE_INITIAL),
            'state2' => new State('state2', StateInterface::TYPE_FINAL)
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
            function (StatefulSubjectInterface $subject) {
                return false;
            }
        );

        $states = [
            'state1' => new State('state1', StateInterface::TYPE_INITIAL),
            'state2' => new State('state2', StateInterface::TYPE_FINAL)
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
            function (StatefulSubjectInterface $subject) {
                return true;
            }
        );

        $states = [
            'state1' => new State('state1', StateInterface::TYPE_INITIAL),
            'state2' => new State('state2'),
            'state2' => new State('state3', StateInterface::TYPE_FINAL)
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
