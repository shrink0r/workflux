<?php

namespace Workflux;

use Workflux\Error\CorruptExecutionFlow;
use Workflux\Error\InvalidWorkflowStructure;
use Workflux\Error\UnsupportedState;
use Workflux\Param\Input;
use Workflux\Param\InputInterface;
use Workflux\Param\OutputInterface;
use Workflux\StateMachineInterface;
use Workflux\State\StateInterface;
use Workflux\State\StateMap;
use Workflux\State\StateSet;
use Workflux\Transition\StateTransitions;
use Workflux\Transition\TransitionInterface;
use Workflux\Transition\TransitionSet;

final class StateMachine implements StateMachineInterface
{
    /**
     * @var StateMap $states
     */
    private $states;

    /**
     * @var StateTransitions $transitions
     */
    private $transitions;

    /**
     * @var StateInterface $initial_state
     */
    private $initial_state;

    /**
     * @var StateMap $final_states
     */
    private $final_states;

    /**
     * @param StateSet $states
     * @param TransitionSet $transitions
     */
    public function __construct(StateSet $states, TransitionSet $transitions)
    {
        $this->states = new StateMap;
        $this->final_states = new StateMap;
        foreach ($states as $state) {
            if ($state->isInitial()) {
                if ($this->initial_state !== null) {
                    throw new InvalidWorkflowStructure('Trying to add more than one initial state.');
                }
                $this->initial_state = $state;
            } elseif ($state->isFinal()) {
                $this->final_states = $this->final_states->put($state);
            }
            $this->states = $this->states->put($state);
        }
        if (!$this->initial_state) {
            throw new InvalidWorkflowStructure('Trying to create statemachine without an initial state.');
        }
        if ($this->getFinalStates()->count() === 0) {
            throw new InvalidWorkflowStructure('Trying to create statemachine without at least one final state.');
        }

        $this->transitions = new StateTransitions;
        foreach ($transitions as $transition) {
            $from_state = $transition->getFrom();
            $to_state = $transition->getTo();
            if (!$this->states->has($from_state)) {
                throw new InvalidWorkflowStructure('Trying to add transition start from unknown state: '.$from_state);
            }
            if (!$this->states->has($to_state)) {
                throw new InvalidWorkflowStructure('Trying to add transition to unknown state: '.$to_state);
            }
            $this->transitions = $this->transitions->put($transition);
        }

        $reachable_states = $this->depthFirstScan($this->initial_state, new StateSet);
        if ($reachable_states->count() !== $this->states->count()) {
            throw new InvalidWorkflowStructure('Not all states are properly connected.');
        }
    }

    /**
     * @param InputInterface $input
     * @param string $start_state
     *
     * @return OutputInterface
     */
    public function execute(InputInterface $input, string $start_state): OutputInterface
    {
        $current_state = $this->getState($start_state);
        if ($current_state->isFinal()) {
            throw new CorruptExecutionFlow("Trying to (re)execute statemachine at final state: ".$start_state);
        }

        do {
            $output = $current_state->execute($input);
            $current_state = null;
            foreach ($this->getStateTransitions($output->getCurrentState()) as $transition) {
                if ($transition->isActivatedBy($input, $output)) {
                    if ($current_state !== null) {
                        throw new CorruptExecutionFlow(
                            'Trying to activate more than one transition at a time. Transition: '.
                            $output->getCurrentState().' -> '.$current_state->getName().' was activated first. '.
                            'Now '.$transition->getFrom().' -> '.$transition->getTo().' is being activated too.'
                        );
                    }
                    $current_state = $this->getState($transition->getTo());
                    $input = Input::fromOutput($output);
                }
            }
        } while ($current_state !== null && !$current_state->isBreakpoint());

        if ($current_state && !($current_state->isFinal() || $current_state->isBreakpoint())) {
            throw new CorruptExecutionFlow(
                'Trying to halt statemachine on an unexpected state: '.$current_state->getName().
                '. Pausing execution is only allowed on FinalStates and Breakpoints.'
            );
        }

        return $current_state && $current_state->isBreakpoint()
            ? $output->withCurrentState($current_state->getName())
            : $output;
    }

    /**
     * @return StateMap
     */
    public function getStates(): StateMap
    {
        return $this->states;
    }

    /**
     * @return StateInterface
     */
    public function getInitialState(): StateInterface
    {
        return $this->initial_state;
    }

    /**
     * @return StateMap
     */
    public function getFinalStates(): StateMap
    {
        return $this->final_states;
    }

    /**
     * @param string $state_name
     *
     * @return StateInterface
     */
    public function getState(string $state_name): StateInterface
    {
        if (!$this->states->has($state_name)) {
            throw new UnsupportedState('Trying to obtain unknown state: '.$state_name);
        }
        return $this->states->get($state_name);
    }

    /**
     * @param string $state_name
     *
     * @return TransitionSet
     */
    public function getStateTransitions(string $state_name): TransitionSet
    {
        if (!$this->states->has($state_name)) {
            throw new UnsupportedState('Trying to obtain transitions for unknown state: '.$state_name);
        }

        return $this->transitions->get($state_name, new TransitionSet);
    }

    /**
     * @return StateTransitions
     */
    public function getTransitions(): StateTransitions
    {
        return $this->transitions;
    }

    /**
     * @param StateInterface $state
     * @param StateSet $visited_states
     *
     * @return StateSet
     */
    protected function depthFirstScan(StateInterface $state, StateSet $visited_states): StateSet
    {
        if ($visited_states->contains($state)) {
            return $visited_states;
        }
        $visited_states->add($state);

        $child_states = array_map(
            function (TransitionInterface $transition) {
                return $this->getState($transition->getTo());
            },
            $this->getStateTransitions($state->getName())->toArray()
        );
        foreach ($child_states as $child_state) {
            $visited_states = $this->depthFirstScan($child_state, $visited_states);
        }

        return $visited_states;
    }

    public function __clone()
    {
        $this->states = clone $this->states;
        $this->transitions = clone $this->transitions;
        $this->initial_state = clone $this->initial_state;
        $this->final_states = clone $this->final_states;
    }
}
