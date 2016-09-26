<?php

namespace Workflux;

use Ds\Map;

class StateMachine implements StateMachineInterface
{
    /**
     * @var StateSet $states
     */
    private $states;

    /**
     * @var TransitionSet $transitions
     */
    private $transitions;

    /**
     * @var string $initial_state
     */
    private $initial_state;

    /**
     * @param StateSet $states
     * @param TransitionSet $transitions
     */
    public function __construct(StateSet $states, TransitionSet $transitions)
    {
        $this->states = new Map;
        foreach ($states as $state) {
            if ($state->isInitial()) {
                if ($this->initial_state !== null) {
                    throw new Error('Trying to add more than one initial state.');
                }
                $this->initial_state = $state;
            }
            $this->states->put($state->getName(), $state);
        }

        $this->transitions = new Map;
        foreach ($transitions as $transition) {
            $from_state = $transition->getFrom();
            $to_state = $transition->getTo();
            if (!$this->states->hasKey($from_state)) {
                throw new Error('Trying to add transition start for unknown state: ' . $from_state);
            }
            if (!$this->states->hasKey($to_state)) {
                throw new Error('Trying to add transition target for unknown state: ' . $to_state);
            }
            $this->transitions->put(
                $from_state,
                $this->transitions->get($from_state, new TransitionSet)->add($transition)
            );
        }

        $reachable_states = $this->depthFirstScan($this->initial_state);
        if (count($reachable_states) !== $this->states->count()) {
            throw new Error('Not all states are properly connected.');
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
            throw new Error("Trying to execute already finished statemachine at final state: " . $start_state);
        }
        do {
            $output = $current_state->execute($input);
            $current_state = null;
            foreach ($this->getStateTransitions($output->getCurrentState()) as $transition) {
                if ($transition->isActivatedBy($input, $output)) {
                    if ($current_state !== null) {
                        throw new Error(
                            'Trying to activate more than one transition at a time. Transition: ' .
                            $output->getCurrentState() . ' -> ' . $current_state->getName() . ' was activated first.' .
                            ' Now ' . $transition->getFrom() . ' -> ' . $transition->getTo() . ' is being activated too.'
                        );
                    }
                    $current_state = $this->getState($transition->getTo());
                    $input = Input::fromOutput($output);
                }
            }
        } while ($current_state !== null && !$current_state->isBreakpoint());

        if ($current_state && !($current_state->isFinal() || $current_state->isBreakpoint())) {
            throw new Error(
                'Trying to halt statemachine on an unexpected state: ' . $current_state->getName() .
                '. Pausing execution is only allowed on FinalStates and Breakpoints.'
            );
        }

        return $current_state && $current_state->isBreakpoint()
            ? $output->withCurrentState($current_state->getName())
            : $output;
    }

    /**
     * @return StateSet
     */
    public function getStates(): StateSet
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
     * @return StateSet
     */
    public function getFinalStates(): StateSet
    {
        return $this->states->filter(function (StateInterface $state): bool {
            return $state->isFinal();
        });
    }

    /**
     * @param string $state_name
     *
     * @return StateInterface
     */
    public function getState(string $state_name): StateInterface
    {
        if (!$this->states->hasKey($state_name)) {
            throw new Error('Trying to obtain unknown state: ' . $state_name);
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
        if (!$this->states->hasKey($state_name)) {
            throw new Error('Trying to obtain transitions for unknown state: ' . $state_name);
        }

        return $this->transitions->get($state_name, new TransitionSet);
    }

    /**
     * @return TransitionSet
     */
    public function getTransitions(): TransitionSet
    {
        return $this->transitions;
    }

    /**
     * @param StateInterface $state
     * @param string[] $visited_states
     *
     * @return string[]
     */
    protected function depthFirstScan(StateInterface $state, array $visited_states = []): array
    {
        if (in_array($state, $visited_states, true)) {
            return $visited_states;
        }
        $visited_states[] = $state;

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
}
