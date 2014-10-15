<?php

namespace Workflux\Builder;

use Workflux\State\StateInterface;
use Workflux\StateMachine\StateMachineInterface;
use Workflux\StateMachine\StateMachine;
use Workflux\Transition\TransitionInterface;
use Workflux\Error\VerificationError;
use Params\Immutable\ImmutableOptionsTrait;
use Params\Immutable\ImmutableOptions;

/**
 * The StateMachineBuilder provides a fluent api for defining state machines.
 * The builder verifies the setup before creating the state machine,
 * which makes it easier to spot errors when building automata.
 */
class StateMachineBuilder implements StateMachineBuilderInterface
{
    use ImmutableOptionsTrait;

    /**
     * @var string $state_machine_name
     */
    protected $state_machine_name;

    /**
     * @var string $state_machine_class
     */
    protected $state_machine_class;

    /**
     * @var array $states
     */
    protected $states;

    /**
     * @var array $transitions
     */
    protected $transitions;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = new ImmutableOptions($options);

        $this->states = [];
        $this->transitions = [];
    }

    /**
     * Sets the state machine's name.
     *
     * @param string $state_machine_name
     *
     * @return StateMachineBuilderInterface
     */
    public function setStateMachineName($state_machine_name)
    {
        $name_regex = '/^[a-zA-Z0-9_]+$/';

        if (!preg_match($name_regex, $state_machine_name)) {
            throw new VerificationError(
                sprintf(
                    'Invalid statemachine name "%s" given.' .
                    ' Only letters, digits and unserscore are permitted.',
                    $state_machine_name
                )
            );
        }

        $this->state_machine_name = $state_machine_name;

        return $this;
    }

    /**
     * Sets the state machine's class/implementor.
     *
     * @param string $state_machine_class
     *
     * @return StateMachineBuilderInterface
     */
    public function setStateMachineClass($state_machine_class)
    {
        if (!class_exists($state_machine_class)) {
            throw new VerificationError(
                sprintf('Unable to load state machine class "%s".', $state_machine_class)
            );
        }

        $this->state_machine_class = $state_machine_class;

        return $this;
    }

    /**
     * Adds the given state to the state machine setup.
     *
     * @param StateInterface $state
     *
     * @return StateMachineBuilderInterface
     */
    public function addState(StateInterface $state)
    {
        $state_name = $state->getName();

        if (isset($this->states[$state_name])) {
            throw new VerificationError(
                sprintf(
                    'A state with the name "%s" already has been added.' .
                    ' State names must be unique within each StateMachine.',
                    $state_name
                )
            );
        }

        $this->states[$state_name] = $state;

        return $this;
    }

    /**
     * Adds the given states to the state machine setup.
     *
     * @param array $states An array of StateInterface instances.
     *
     * @return StateMachineBuilderInterface
     */
    public function addStates(array $states)
    {
        foreach ($states as $state) {
            $this->addState($state);
        }

        return $this;
    }

    /**
     * Adds a single transition to the state machine setup for a given event.
     *
     * @param TransitionInterface $transition
     * @param string $event_name If the event name is omitted, then the transition will act as sequential.
     *
     * @return StateMachineBuilderInterface
     */
    public function addTransition(TransitionInterface $transition, $event_name = '')
    {
        $transition_key = $event_name ?: StateMachine::SEQ_TRANSITIONS_KEY;

        foreach ($transition->getIncomingStateNames() as $state_name) {
            if (!isset($this->transitions[$state_name])) {
                $this->transitions[$state_name] = [];
            }

            if (!isset($this->transitions[$state_name][$transition_key])) {
                $this->transitions[$state_name][$transition_key] = [];
            }

            if (in_array($transition, $this->transitions[$state_name][$transition_key], true)) {
                throw new VerificationError('Adding the same transition instance twice is not supported.');
            }

            $this->transitions[$state_name][$transition_key][] = $transition;
        }

        return $this;
    }

    /**
     * Convenience method for adding multiple event-transition combinations at once.
     * This method does not work for adding sequential transitions, because they don't have an event.
     *
     * @param array $event_transitions The array is expected too be structured as followed by example:
     *
     * <code>
     * [
     *     $event_name => [ $transition1, $transition1 ],
     *     $other_event_name => $other_transition, // you can add either add an array of transitions or just one
     *     ...
     * ]
     * </code>
     *
     * @return StateMachineBuilderInterface
     */
    public function addTransitions(array $event_transitions)
    {
        foreach ($event_transitions as $event_name => $transition_or_transitions) {
            if (!is_array($transition_or_transitions)) {
                $transitions = [ $transition_or_transitions ];
            } else {
                $transitions = $transition_or_transitions;
            }

            foreach ($transitions as $transition) {
                $this->addTransition($transition, $event_name);
            }
        }

        return $this;
    }

    /**
     * Verifies the builder's current state and builds a state machine off of it.
     *
     * @return StateMachineInterface
     */
    public function build()
    {
        $this->verifyStateGraph();

        $state_machine = $this->createStateMachine();

        $this->tearDown();

        return $state_machine;
    }

    /**
     * Asserts that the builder's current state reflects a valid state machine configuration.
     *
     * @throws VerificationError
     */
    protected function verifyStateGraph()
    {
        if (!$this->state_machine_name) {
            throw new VerificationError(
                'Required state machine name is missing. Make sure to call setStateMachineName.'
            );
        }

        foreach ($this->getVerifications() as $verification) {
            $verification->verify();
        }
    }

    /**
     * Creates a new state machine based on the builder's current state.
     *
     * @return StateMachineInterface
     */
    protected function createStateMachine()
    {
        if (!$this->state_machine_class) {
            $state_machine_class = StateMachine::CLASS;
        } else {
            $state_machine_class = $this->state_machine_class;
        }

        $state_machine = new $state_machine_class($this->state_machine_name, $this->states, $this->transitions);

        if (!$state_machine instanceof StateMachineInterface) {
            throw new VerificationError(
                sprintf(
                    'The given state machine class "%s" does not implement the required interface "%s"',
                    $state_machine_class,
                    StateMachineInterface::CLASS
                )
            );
        }

        return $state_machine;
    }

    /**
     * Resets the builder's internal state, so you can start building a new state machine.
     */
    protected function tearDown()
    {
        $this->state_machine_name = null;
        $this->states = [];
        $this->transitions = [];
    }

    /**
     * Returns a list of verifications that are run before a new state machine is created.
     *
     * @return array An array of VerficationInterface instances.
     */
    protected function getVerifications()
    {
        return [
            new StatesVerification($this->states, $this->transitions),
            new TransitionsVerification($this->states, $this->transitions)
        ];
    }
}
