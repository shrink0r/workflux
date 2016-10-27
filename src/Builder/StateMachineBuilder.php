<?php

namespace Workflux\Builder;

use Ds\Map;
use Workflux\Error\UnsupportedState;
use Workflux\Error\WorkfluxError;
use Workflux\StateMachine;
use Workflux\StateMachineInterface;
use Workflux\State\StateInterface;
use Workflux\State\StateSet;
use Workflux\Transition\TransitionInterface;
use Workflux\Transition\TransitionSet;

final class StateMachineBuilder
{
    /**
     * @var Map $states
     */
    private $states;

    /**
     * @var Map $states
     */
    private $transitions;

    /**
     * @var string $state_machine_name
     */
    private $state_machine_name;

    public function __construct()
    {
        $this->states = new Map;
        $this->transitions = new Map;
    }

    /**
     * @param string $state_machine_name
     *
     * @return self
     */
    public function addStateMachineName(string $state_machine_name): self
    {
        $builder = clone $this;
        $builder->state_machine_name = $state_machine_name;

        return $builder;
    }

    /**
     * @param StateInterface $state
     *
     * @return self
     */
    public function addState(StateInterface $state): self
    {
        $builder = clone $this;
        $builder->states[$state->getName()] = $state;

        return $builder;
    }

    /**
     * @param StateInterface[] $states
     *
     * @return self
     */
    public function addStates(array $states): self
    {
        $builder = clone $this;
        foreach ($states as $state) {
            $builder->states[$state->getName()] = $state;
        }

        return $builder;
    }

    /**
     * @param TransitionInterface $transition
     *
     * @return self
     */
    public function addTransition(TransitionInterface $transition): self
    {
        if (!$this->states->hasKey($transition->getFrom())) {
            throw new UnsupportedState('Trying to add transition from unknown state: ' . $transition->getFrom());
        }
        if (!$this->states->hasKey($transition->getTo())) {
            throw new UnsupportedState('Trying to add transition to unknown state: ' . $transition->getTo());
        }
        $transition_key = $transition->getFrom().$transition->getTo();
        if ($this->transitions->hasKey($transition_key)) {
            throw new InvalidWorkflow(
                sprintf('Trying to add same transition twice: %s -> %s', $transition->getFrom(), $transition->getTo())
            );
        }
        $builder = clone $this;
        $builder->transitions[$transition_key] = $transition;

        return $builder;
    }

    /**
     * @param TransitionInterface[] $transitions
     *
     * @return self
     */
    public function addTransitions(array $transitions): self
    {
        $builder = clone $this;
        foreach ($transitions as $transition) {
            if (!$this->states->hasKey($transition->getFrom())) {
                throw new UnsupportedState('Trying to add transition from unknown state: ' . $transition->getFrom());
            }
            if (!$this->states->hasKey($transition->getTo())) {
                throw new UnsupportedState('Trying to add transition to unknown state: ' . $transition->getTo());
            }
            $transition_key = $transition->getFrom().$transition->getTo();
            if ($builder->transitions->hasKey($transition_key)) {
                throw new InvalidWorkflow(
                    sprintf(
                        'Trying to add same transition twice: %s -> %s',
                        $transition->getFrom(),
                        $transition->getTo()
                    )
                );
            }
            $builder->transitions[$transition_key] = $transition;
        }
        return $builder;
    }

    /**
     * @return self
     */
    public function build(string $class): StateMachineInterface
    {
        $states = new StateSet($this->states->values()->toArray());
        $transitions = new TransitionSet($this->transitions->values()->toArray());
        if (!in_array(StateMachineInterface::CLASS, class_implements($class))) {
            throw new WorkfluxError(
                'Trying to build statemachine that does not implement ' . StateMachineInterface::CLASS
            );
        }
        return new StateMachine($this->state_machine_name, $states, $transitions);
    }
}
