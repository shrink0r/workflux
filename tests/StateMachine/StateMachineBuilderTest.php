<?php

namespace Workflux\Tests\StateMachine;

use Workflux\Error;
use Workflux\Tests\BaseTestCase;
use Workflux\StateMachine\IStateMachine;
use Workflux\StateMachine\StateMachine;
use Workflux\StateMachine\StateMachineBuilder;
use Workflux\State\IState;
use Workflux\State\State;
use Workflux\Transition\Transition;
use Workflux\Tests\Fixture\GenericSubject;
use Workflux\Renderer\DotGraphRenderer;
use Workflux\Tests\StateMachine\Fixture\InvalidStateMachine;

class StateMachineBuilderTest extends BaseTestCase
{
    const MACHINE_NAME = 'test_machine';

    const STATE_EDITING = 'editing';

    const STATE_APPROVAL = 'approval';

    const STATE_PUBLISHED = 'published';

    const STATE_DELETED = 'deleted';

    public function testBuild()
    {
        $states = [
            new State(self::STATE_EDITING, IState::TYPE_INITIAL),
            new State(self::STATE_APPROVAL),
            new State(self::STATE_PUBLISHED),
            new State(self::STATE_DELETED, IState::TYPE_FINAL)
        ];

        $transitons = [
            new Transition('promote', [ self::STATE_EDITING ], self::STATE_APPROVAL),
            new Transition('promote', [ self::STATE_APPROVAL ], self::STATE_PUBLISHED),
            new Transition('demote', [ self::STATE_PUBLISHED, self::STATE_APPROVAL ], self::STATE_EDITING),
            new Transition(
                'delete',
                [ self::STATE_EDITING, self::STATE_APPROVAL, self::STATE_PUBLISHED ],
                self::STATE_DELETED
            )
        ];

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransitions($transitons)
            ->build();

        $this->assertEquals(self::MACHINE_NAME, $state_machine->getName());
        $this->assertEquals($transitons[0], $state_machine->getTransition(self::STATE_EDITING, 'promote'));
        $this->assertEquals($states[0], $state_machine->getState(self::STATE_EDITING));
        $this->assertEquals($states[1], $state_machine->getState(self::STATE_APPROVAL));
    }

    public function testIncompleteSecondBuild()
    {
        $this->setExpectedException(
            Error::CLASS,
            'Required state machine name is missing. Make sure to call setStateMachineName.'
        );

        $states = [
            new State(self::STATE_EDITING, IState::TYPE_INITIAL),
            new State(self::STATE_PUBLISHED, IState::TYPE_FINAL)
        ];

        $transiton = new Transition('promote', [ self::STATE_EDITING ], self::STATE_PUBLISHED);

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition($transiton)
            ->build();

        $builder->build();
    }

    public function testDuplicateState()
    {
        $this->setExpectedException(
            Error::CLASS,
            'A state with the name "editing" already has been added.' .
            ' State names must be unique within each StateMachine.'
        );

        $states = [
            new State(self::STATE_EDITING, IState::TYPE_INITIAL),
            new State(self::STATE_EDITING),
            new State(self::STATE_PUBLISHED, IState::TYPE_FINAL)
        ];

        $builder = new StateMachineBuilder();
        $builder->addStates($states);
    }

    public function testDuplicateTransition()
    {
        $this->setExpectedException(
            Error::CLASS,
            'A transition with the name "promote" already has been added for state "editing".' .
            ' Transition names must be unique within the context of a given state.'
        );

        $transitons = [
            new Transition('promote', [ self::STATE_EDITING ], self::STATE_APPROVAL),
            new Transition('promote', [ self::STATE_EDITING ], self::STATE_PUBLISHED)
        ];

        $builder = new StateMachineBuilder();
        $builder->addTransitions($transitons);
    }

    public function testInvalidOutgoingState()
    {
        $this->setExpectedException(
            Error::CLASS,
            'Unable to find outgoing state "non_existant" for transition "promote". Maybe a typo?'
        );

        $states = [
            new State(self::STATE_EDITING, IState::TYPE_INITIAL),
            new State(self::STATE_PUBLISHED, IState::TYPE_FINAL)
        ];

        $transiton = new Transition('promote', [ self::STATE_EDITING ], 'non_existant');

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition($transiton)
            ->build();
    }

    public function testInvalidIncomingState()
    {
        $this->setExpectedException(
            Error::CLASS,
            'Unable to find incoming state "non_existant" for given transitions. Maybe a typo?'
        );

        $states = [
            new State(self::STATE_EDITING, IState::TYPE_INITIAL),
            new State(self::STATE_PUBLISHED, IState::TYPE_FINAL)
        ];

        $transiton = new Transition('promote', [ 'non_existant' ], self::STATE_PUBLISHED);

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition($transiton)
            ->build();
    }

    public function testMissingInitialState()
    {
        $this->setExpectedException(
            Error::CLASS,
            'No state of type "initial" found, but exactly one initial state is required.'
        );

        $states = [
            new State(self::STATE_EDITING),
            new State(self::STATE_PUBLISHED, IState::TYPE_FINAL)
        ];

        $transiton = new Transition('promote', [ self::STATE_EDITING ], self::STATE_PUBLISHED);

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition($transiton)
            ->build();
    }

    public function testMissingFinalState()
    {
        $this->setExpectedException(
            Error::CLASS,
            'No state of type "final" found, but at least one final state is required.'
        );

        $states = [
            new State(self::STATE_EDITING, IState::TYPE_INITIAL),
            new State(self::STATE_PUBLISHED)
        ];

        $transiton = new Transition('promote', [ self::STATE_EDITING ], self::STATE_PUBLISHED);

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition($transiton)
            ->build();
    }

    public function testTooManyInitialStates()
    {
        $this->setExpectedException(
            Error::CLASS,
            'Only one initial state is supported per state machine definition.' .
            'State "editing" has been previously registered as initial state, so state " cant be added.'
        );

        $states = [
            new State(self::STATE_EDITING, IState::TYPE_INITIAL),
            new State(self::STATE_PUBLISHED, IState::TYPE_INITIAL)
        ];

        $transiton = new Transition('promote', [ self::STATE_EDITING ], self::STATE_PUBLISHED);

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition($transiton)
            ->build();
    }

    public function testMissingStateMachineName()
    {
        $this->setExpectedException(
            Error::CLASS,
            'Required state machine name is missing. Make sure to call setStateMachineName.'
        );

        $states = [
            new State(self::STATE_EDITING, IState::TYPE_INITIAL),
            new State(self::STATE_PUBLISHED, IState::TYPE_FINAL)
        ];

        $transiton = new Transition('promote', [ self::STATE_EDITING ], self::STATE_PUBLISHED);

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->addStates($states)
            ->addTransition($transiton)
            ->build();
    }

    public function testInvalidStateMachineName()
    {
        $this->setExpectedException(
            Error::CLASS,
            'Invalid statemachine name "Erpen Derp!" given. Only letters, digits and unserscore are permitted.'
        );

        $builder = new StateMachineBuilder();
        $builder->setStateMachineName('Erpen Derp!');
    }

    public function testMissingStateMachineClass()
    {
        $this->setExpectedException(
            Error::CLASS,
            'Unable to load state machine class "HeisenStateMachine".'
        );

        $states = [
            new State(self::STATE_EDITING, IState::TYPE_INITIAL),
            new State(self::STATE_PUBLISHED, IState::TYPE_FINAL)
        ];

        $transiton = new Transition('promote', [ self::STATE_EDITING ], self::STATE_PUBLISHED);

        $builder = new StateMachineBuilder([ 'state_machine_class' => 'HeisenStateMachine' ]);
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition($transiton)
            ->build();
    }

    public function testInvalidStateMachineClass()
    {
        $this->setExpectedException(
            Error::CLASS,
            'The given state machine class "Workflux\Tests\StateMachine\Fixture\InvalidStateMachine"' .
            ' does not implement the required interface "Workflux\StateMachine\IStateMachine"'
        );

        $states = [
            new State(self::STATE_EDITING, IState::TYPE_INITIAL),
            new State(self::STATE_PUBLISHED, IState::TYPE_FINAL)
        ];

        $transiton = new Transition('promote', [ self::STATE_EDITING ], self::STATE_PUBLISHED);

        $builder = new StateMachineBuilder([ 'state_machine_class' => InvalidStateMachine::CLASS ]);
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition($transiton)
            ->build();
    }
}
