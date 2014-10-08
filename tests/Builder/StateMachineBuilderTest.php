<?php

namespace Workflux\Tests\Builder;

use Workflux\Error\VerificationError;
use Workflux\StateMachine\StateMachineInterface;
use Workflux\StateMachine\StateMachine;
use Workflux\Builder\StateMachineBuilder;
use Workflux\State\StateInterface;
use Workflux\State\State;
use Workflux\Transition\Transition;
use Workflux\Renderer\DotGraphRenderer;
use Workflux\Tests\BaseTestCase;
use Workflux\Tests\Fixture\GenericSubject;
use Workflux\Tests\StateMachine\Fixture\InvalidStateMachine;

class StateMachineBuilderTest extends BaseTestCase
{
    const MACHINE_NAME = 'test_machine';

    public function testBuild()
    {
        $states = [
            new State('editing', StateInterface::TYPE_INITIAL),
            new State('approval'),
            new State('published'),
            new State('deleted', StateInterface::TYPE_FINAL)
        ];

        $approve = new Transition('editing', 'approval');
        $publish = new Transition('approval', 'published');
        $demote = new Transition([ 'approval', 'published' ], 'editing');
        $delete = new Transition([ 'editing', 'approval', 'published' ], 'deleted');

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransitions([ 'promote' => [ $approve, $publish ], 'demote' => $demote, 'delete' => $delete ])
            ->build();

        $this->assertEquals(self::MACHINE_NAME, $state_machine->getName());
        $this->assertContains($approve, $state_machine->getTransitions('editing', 'promote'));
        $this->assertEquals($states[0], $state_machine->getState('editing'));
        $this->assertEquals($states[1], $state_machine->getState('approval'));
    }

    public function testTooManyTransitions()
    {
        $this->setExpectedException(
            VerificationError::CLASS,
            'Found transitions for both sequential and event based execution.' .
            ' State "approval" may  behave as an event-node or a sequential node, but not both at once.'
        );

        $states = [
            new State('editing', StateInterface::TYPE_INITIAL),
            new State('approval'),
            new State('published', StateInterface::TYPE_FINAL)
        ];

        $approve = new Transition('editing', 'approval');
        $publish = new Transition('approval', 'published');
        $demote = new Transition('approval', 'editing');

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransitions([ 'promote' => [ $approve ], 'demote' => $demote ])
            ->addTransition($publish)
            ->build();

        $this->assertEquals(self::MACHINE_NAME, $state_machine->getName());
        $this->assertContains($approve, $state_machine->getTransitions('editing', 'promote'));
        $this->assertEquals($states[0], $state_machine->getState('editing'));
        $this->assertEquals($states[1], $state_machine->getState('approval'));
    }

    public function testFinalStateWithTransitions()
    {
        $this->setExpectedException(
            VerificationError::CLASS,
            'State "published" is final and may not have any transitions.'
        );

        $states = [
            new State('editing', StateInterface::TYPE_INITIAL),
            new State('approval'),
            new State('published', StateInterface::TYPE_FINAL)
        ];

        $approve = new Transition('editing', 'approval');
        $publish = new Transition('approval', 'published');
        $demote = new Transition([ 'approval', 'published' ], 'editing');

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransitions([ 'promote' => [ $approve ], 'demote' => $demote ])
            ->addTransition($publish)
            ->build();

        $this->assertEquals(self::MACHINE_NAME, $state_machine->getName());
        $this->assertContains($approve, $state_machine->getTransitions('editing', 'promote'));
        $this->assertEquals($states[0], $state_machine->getState('editing'));
        $this->assertEquals($states[1], $state_machine->getState('approval'));
    }

    public function testIncompleteSecondBuild()
    {
        $this->setExpectedException(
            VerificationError::CLASS,
            'Required state machine name is missing. Make sure to call setStateMachineName.'
        );

        $states = [
            new State('editing', StateInterface::TYPE_INITIAL),
            new State('published', StateInterface::TYPE_FINAL)
        ];

        $transiton = new Transition('editing', 'published');

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition($transiton, 'promote')
            ->build();

        $builder->build();
    }

    public function testDuplicateState()
    {
        $this->setExpectedException(
            VerificationError::CLASS,
            'A state with the name "editing" already has been added.' .
            ' State names must be unique within each StateMachine.'
        );

        $states = [
            new State('editing', StateInterface::TYPE_INITIAL),
            new State('editing'),
            new State('published', StateInterface::TYPE_FINAL)
        ];

        $builder = new StateMachineBuilder();
        $builder->addStates($states);
    }

    public function testDuplicateTransition()
    {
        $this->setExpectedException(
            VerificationError::CLASS,
            'Adding the same transition instance twice is not supported.'
        );

        $transiton = new Transition('editing', 'approval');

        $builder = new StateMachineBuilder();
        $builder->addTransition($transiton, 'promote')->addTransition($transiton, 'promote');
    }

    public function testInvalidOutgoingState()
    {
        $this->setExpectedException(
            VerificationError::CLASS,
            'Unable to find outgoing state "non_existant" for transition on event "promote". Maybe a typo?'
        );

        $states = [
            new State('editing', StateInterface::TYPE_INITIAL),
            new State('published', StateInterface::TYPE_FINAL)
        ];

        $transiton = new Transition('editing', 'non_existant');

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition($transiton, 'promote')
            ->build();
    }

    public function testInvalidIncomingState()
    {
        $this->setExpectedException(
            VerificationError::CLASS,
            'Unable to find incoming state "non_existant" for given transitions. Maybe a typo?'
        );

        $states = [
            new State('editing', StateInterface::TYPE_INITIAL),
            new State('published', StateInterface::TYPE_FINAL)
        ];

        $transiton = new Transition('non_existant', 'published');

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition($transiton, 'promote')
            ->build();
    }

    public function testMissingInitialState()
    {
        $this->setExpectedException(
            VerificationError::CLASS,
            'No state of type "initial" found, but exactly one initial state is required.'
        );

        $states = [
            new State('editing'),
            new State('published', StateInterface::TYPE_FINAL)
        ];

        $transiton = new Transition('editing', 'published');

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition($transiton, 'promote')
            ->build();
    }

    public function testMissingFinalState()
    {
        $this->setExpectedException(
            VerificationError::CLASS,
            'No state of type "final" found, but at least one final state is required.'
        );

        $states = [
            new State('editing', StateInterface::TYPE_INITIAL),
            new State('published')
        ];

        $transiton = new Transition([ 'editing', 'published' ], 'published');

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition($transiton, 'promote')
            ->build();
    }

    public function testTooManyInitialStates()
    {
        $this->setExpectedException(
            VerificationError::CLASS,
            'Only one initial state is supported per state machine definition.' .
            'State "editing" has been previously registered as initial state, so state " cant be added.'
        );

        $states = [
            new State('editing', StateInterface::TYPE_INITIAL),
            new State('published', StateInterface::TYPE_INITIAL)
        ];

        $transiton = new Transition([ 'editing' ], 'published');

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition($transiton, 'promote')
            ->build();
    }

    public function testMissingStateMachineName()
    {
        $this->setExpectedException(
            VerificationError::CLASS,
            'Required state machine name is missing. Make sure to call setStateMachineName.'
        );

        $states = [
            new State('editing', StateInterface::TYPE_INITIAL),
            new State('published', StateInterface::TYPE_FINAL)
        ];

        $transiton = new Transition('editing', 'published');

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->addStates($states)
            ->addTransition($transiton, 'promote')
            ->build();
    }

    public function testMissingStateTransitions()
    {
        $this->setExpectedException(
            VerificationError::CLASS,
            'State "transcoding" is expected to have at least one transition.' .
            ' Only "final" states are permitted to have no transitions.'
        );

        $states = [
            new State('editing', StateInterface::TYPE_INITIAL),
            new State('transcoding'),
            new State('published', StateInterface::TYPE_FINAL)
        ];

        $transiton = new Transition('editing', 'published');

        $builder = new StateMachineBuilder([ 'state_machine_class' => 'HeisenStateMachine' ]);
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition($transiton, 'promote')
            ->build();
    }

    public function testInvalidStateMachineClass()
    {
        $this->setExpectedException(
            VerificationError::CLASS,
            'The given state machine class "Workflux\Tests\StateMachine\Fixture\InvalidStateMachine"' .
            ' does not implement the required interface "Workflux\StateMachine\StateMachineInterface"'
        );

        $states = [
            new State('editing', StateInterface::TYPE_INITIAL),
            new State('published', StateInterface::TYPE_FINAL)
        ];

        $transiton = new Transition('editing', 'published');

        $builder = new StateMachineBuilder([ 'state_machine_class' => InvalidStateMachine::CLASS ]);
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition($transiton, 'promote')
            ->build();
    }

    public function testInvalidStateMachineName()
    {
        $this->setExpectedException(
            VerificationError::CLASS,
            'Invalid statemachine name "Erpen Derp!" given. Only letters, digits and unserscore are permitted.'
        );

        $builder = new StateMachineBuilder();
        $builder->setStateMachineName('Erpen Derp!');
    }

    public function testMissingStateMachineClass()
    {
        $this->setExpectedException(
            VerificationError::CLASS,
            'Unable to load state machine class "HeisenStateMachine".'
        );

        $states = [
            new State('editing', StateInterface::TYPE_INITIAL),
            new State('published', StateInterface::TYPE_FINAL)
        ];

        $transiton = new Transition('editing', 'published');

        $builder = new StateMachineBuilder([ 'state_machine_class' => 'HeisenStateMachine' ]);
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition($transiton, 'promote')
            ->build();
    }
}
