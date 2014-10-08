<?php

namespace Workflux\Builder;

use Workflux\State\StateInterface;
use Workflux\StateMachine\StateMachineInterface;
use Workflux\StateMachine\StateMachine;
use Workflux\Transition\TransitionInterface;
use Workflux\Error\VerificationError;
use Params\Immutable\ImmutableOptionsTrait;
use Params\Immutable\ImmutableOptions;

class StateMachineBuilder implements StateMachineBuilderInterface
{
    use ImmutableOptionsTrait;

    protected $state_machine_name;

    protected $states;

    protected $transitions;

    public function __construct(array $options = [])
    {
        $this->options = new ImmutableOptions($options);

        $this->states = [];
        $this->transitions = [];
    }

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

    public function addStates(array $states)
    {
        foreach ($states as $state) {
            $this->addState($state);
        }

        return $this;
    }

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

    public function addTransitions(array $events)
    {
        foreach ($events as $event_name => $transition_or_transitions) {
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

    public function build()
    {
        $this->verifyStateGraph();

        $state_machine_class = $this->getOption('state_machine_class', StateMachine::CLASS);

        if (!class_exists($state_machine_class)) {
            throw new VerificationError(
                sprintf('Unable to load state machine class "%s".', $state_machine_class)
            );
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

        $this->clearIntrinsicState();

        return $state_machine;
    }

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

    protected function clearIntrinsicState()
    {
        $this->state_machine_name = null;
        $this->states = [];
        $this->transitions = [];
    }

    protected function getVerifications()
    {
        return [
            new StatesVerification($this->states, $this->transitions),
            new TransitionsVerification($this->states, $this->transitions)
        ];
    }
}
