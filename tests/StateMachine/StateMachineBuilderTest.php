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

    public function testBuild()
    {
        $states = [
            new State('editing', IState::TYPE_INITIAL),
            new State('approval'),
            new State('published'),
            new State('deleted', IState::TYPE_FINAL)
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

    public function testIncompleteSecondBuild()
    {
        $this->setExpectedException(
            Error::CLASS,
            'Required state machine name is missing. Make sure to call setStateMachineName.'
        );

        $states = [
            new State('editing', IState::TYPE_INITIAL),
            new State('published', IState::TYPE_FINAL)
        ];

        $transiton = new Transition('editing', 'published');

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition('promote', $transiton)
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
            new State('editing', IState::TYPE_INITIAL),
            new State('editing'),
            new State('published', IState::TYPE_FINAL)
        ];

        $builder = new StateMachineBuilder();
        $builder->addStates($states);
    }

    public function testDuplicateTransition()
    {
        $this->setExpectedException(
            Error::CLASS,
            'Adding the same transition instance twice is not supported.'
        );

        $transiton = new Transition('editing', 'approval');

        $builder = new StateMachineBuilder();
        $builder->addTransition('promote', $transiton)->addTransition('promote', $transiton);
    }

    public function testInvalidOutgoingState()
    {
        $this->setExpectedException(
            Error::CLASS,
            'Unable to find outgoing state "non_existant" for transition on event "promote". Maybe a typo?'
        );

        $states = [
            new State('editing', IState::TYPE_INITIAL),
            new State('published', IState::TYPE_FINAL)
        ];

        $transiton = new Transition('editing', 'non_existant');

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition('promote', $transiton)
            ->build();
    }

    public function testInvalidIncomingState()
    {
        $this->setExpectedException(
            Error::CLASS,
            'Unable to find incoming state "non_existant" for given transitions. Maybe a typo?'
        );

        $states = [
            new State('editing', IState::TYPE_INITIAL),
            new State('published', IState::TYPE_FINAL)
        ];

        $transiton = new Transition('non_existant', 'published');

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition('promote', $transiton)
            ->build();
    }

    public function testMissingInitialState()
    {
        $this->setExpectedException(
            Error::CLASS,
            'No state of type "initial" found, but exactly one initial state is required.'
        );

        $states = [
            new State('editing'),
            new State('published', IState::TYPE_FINAL)
        ];

        $transiton = new Transition('editing', 'published');

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition('promote', $transiton)
            ->build();
    }

    public function testMissingFinalState()
    {
        $this->setExpectedException(
            Error::CLASS,
            'No state of type "final" found, but at least one final state is required.'
        );

        $states = [
            new State('editing', IState::TYPE_INITIAL),
            new State('published')
        ];

        $transiton = new Transition([ 'editing' ], 'published');

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition('promote', $transiton)
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
            new State('editing', IState::TYPE_INITIAL),
            new State('published', IState::TYPE_INITIAL)
        ];

        $transiton = new Transition([ 'editing' ], 'published');

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition('promote', $transiton)
            ->build();
    }

    public function testMissingStateMachineName()
    {
        $this->setExpectedException(
            Error::CLASS,
            'Required state machine name is missing. Make sure to call setStateMachineName.'
        );

        $states = [
            new State('editing', IState::TYPE_INITIAL),
            new State('published', IState::TYPE_FINAL)
        ];

        $transiton = new Transition('editing', 'published');

        $builder = new StateMachineBuilder();
        $state_machine = $builder
            ->addStates($states)
            ->addTransition('promote', $transiton)
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
            new State('editing', IState::TYPE_INITIAL),
            new State('published', IState::TYPE_FINAL)
        ];

        $transiton = new Transition('editing', 'published');

        $builder = new StateMachineBuilder([ 'state_machine_class' => 'HeisenStateMachine' ]);
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition('promote', $transiton)
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
            new State('editing', IState::TYPE_INITIAL),
            new State('published', IState::TYPE_FINAL)
        ];

        $transiton = new Transition('editing', 'published');

        $builder = new StateMachineBuilder([ 'state_machine_class' => InvalidStateMachine::CLASS ]);
        $state_machine = $builder
            ->setStateMachineName(self::MACHINE_NAME)
            ->addStates($states)
            ->addTransition('promote', $transiton)
            ->build();
    }
}
