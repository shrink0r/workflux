<?php

namespace Workflux;

class StateMachine implements StateMachineInterface
{
    private $states;

    private $transitions;

    private $initial_state;

    public function __construct(array $states, array $transitions)
    {
        $this->states = [];
        foreach ($states as $state) {
            if ($state->isInitial()) {
                if ($this->initial_state !== null) {
                    throw new Error('Trying to add more than one initial state.');
                }
                $this->initial_state = $state;
            }
            $this->states[$state->getName()] = $state;
        }

        $this->transitions = [];
        foreach ($transitions as $transition) {
            $in = $transition->getIn();
            $out = $transition->getOut();
            if (!isset($this->transitions[$in])) {
                $this->transitions[$in] = [];
            }
            if (!isset($this->states[$in])) {
                throw new Error('Trying to add transition for unknown (in)state: ' . $in);
            }
            if (!isset($this->states[$out])) {
                throw new Error('Trying to add transition for unknown (out)state: ' . $out);
            }
            $this->transitions[$in][] = $transition;
        }
        $visited_states = $this->depthFirstScan($this->initial_state);
        if (count($visited_states) !== count($this->states)) {
            throw new Error('Not all states are properly connected.');
        }
    }

    public function execute(InputInterface $input, $start_state)
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
                            ' Now ' . $transition->getIn() . ' -> ' . $transition->getOut() . ' is being activated too.'
                        );
                    }
                    $current_state = $this->getState($transition->getOut());
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

    public function getStates()
    {
        return $this->states;
    }

    public function getInitialState()
    {
        return $this->initial_state;
    }

    public function getFinalStates()
    {
        return array_filter(function (StateInterface $state) {
            return $state-isFinal();
        }, $this->states);
    }

    public function getState($state_name)
    {
        if (!isset($this->states[$state_name])) {
            throw new Error('Trying to obtain unknown state: ' . $state_name);
        }
        return $this->states[$state_name];
    }

    public function getStateTransitions($state_name)
    {
        if (!isset($this->states[$state_name])) {
            throw new Error('Trying to obtain transitions for unknown state: ' . $state_name);
        }

        return isset($this->transitions[$state_name]) ? $this->transitions[$state_name] : [];
    }

    public function getTransitions()
    {
        return $this->transitions;
    }

    protected function depthFirstScan(StateInterface $state, array $visited_states = [])
    {
        if (in_array($state, $visited_states, true)) {
            return $visited_states;
        }
        $visited_states[] = $state;

        $child_states = array_map(
            function (TransitionInterface $transition) {
                return $this->getState($transition->getOut());
            },
            $this->getStateTransitions($state->getName())
        );
        foreach ($child_states as $child_state) {
            $visited_states = $this->depthFirstScan($child_state, $visited_states);
        }

        return $visited_states;
    }
}
