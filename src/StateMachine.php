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
     * @var StateTransitions $state_transitions
     */
    private $state_transitions;

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
        list($initial_state, $all_states, $final_states) = $this->adoptStates($states);
        $state_transitions = $this->adoptStateTransitions($all_states, $transitions);
        $reachable_states = $this->depthFirstScan($all_states, $state_transitions, $initial_state, new StateSet);

        if (count($reachable_states) !== count($all_states)) {
            throw new InvalidWorkflowStructure('Not all states are properly connected.');
        }

        $this->initial_state = $initial_state;
        $this->states = $all_states;
        $this->final_states = $final_states;
        $this->state_transitions = $state_transitions;
    }

    /**
     * @param InputInterface $input
     * @param string $state_name
     *
     * @return OutputInterface
     */
    public function execute(InputInterface $input, string $state_name): OutputInterface
    {
        // @todo this needs to be configurable somehow; maybe a good ol' "define" or an "env var" might do?
        static $max_allowed_executions = 100;

        $bread_crumbs = [];
        $next_state = $this->getStartStateByName($state_name);

        do {
            $bread_crumbs[] = $next_state->getName();
            $output = $next_state->execute($input);
            $next_state = $this->activateTransition($input, $output);
            $input = Input::fromOutput($output);
            // @todo this needs a better runtime-cycle detetection than just counting max executions.
            // maybe somehow use the bread-crumbs to find reoccuring path patterns?
        } while ($next_state && !$next_state->isBreakpoint() && count($bread_crumbs) < $max_allowed_executions);

        if (count($bread_crumbs) === $max_allowed_executions) {
            // @todo would be nice to collapse recursive paths in the output
            // in order to prevent the ridiculous length of the exception while still providing some insight.
            throw new CorruptExecutionFlow(
                "Trying to execute more than the allowed number of max: $max_allowed_executions workflow steps.\n".
                "It is likely that an intentional cycle inside the workflow isn't properly exiting. ".
                "The executed states where:\n".implode(' -> ', $bread_crumbs)
            );
        }

        return $next_state ? $output->withCurrentState($next_state->getName()) : $output;
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
     * @return StateTransitions
     */
    public function getStateTransitions(): StateTransitions
    {
        return $this->state_transitions;
    }

    /**
     * @param  StateSet $state_set
     *
     * @return mixed[]
     */
    private function adoptStates(StateSet $state_set): array
    {
        $initial_state = null;
        $all_states = new StateMap;
        $final_states = new StateMap;

        foreach ($state_set as $state) {
            if ($state->isInitial()) {
                if ($initial_state !== null) {
                    throw new InvalidWorkflowStructure('Trying to add more than one initial state.');
                }
                $initial_state = $state;
            }

            if ($state->isFinal()) {
                if ($state->isInitial()) {
                    throw new InvalidWorkflowStructure('Trying to add state as initial and final at the same time.');
                }
                $final_states = $final_states->put($state);
            }

            $all_states = $all_states->put($state);
        }

        if (!$initial_state) {
            throw new InvalidWorkflowStructure('Trying to create statemachine without an initial state.');
        }
        if ($final_states->count() === 0) {
            throw new InvalidWorkflowStructure('Trying to create statemachine without at least one final state.');
        }

        return [ $initial_state, $all_states, $final_states ];
    }

    /**
     * @param  StateMap $states
     * @param  TransitionSet $transitions
     *
     * @return StateTransitions
     */
    private function adoptStateTransitions(StateMap $states, TransitionSet $transitions)
    {
        $state_transitions = new StateTransitions;

        foreach ($transitions as $transition) {
            $from_state = $transition->getFrom();
            $to_state = $transition->getTo();

            if (!$states->has($from_state)) {
                throw new InvalidWorkflowStructure('Trying to transition from unknown state: '.$from_state);
            }
            if ($states->get($from_state)->isFinal()) {
                throw new InvalidWorkflowStructure('Trying to transition from final-state: '.$from_state);
            }

            if (!$states->has($to_state)) {
                throw new InvalidWorkflowStructure('Trying to transition to unknown state: '.$to_state);
            }
            if ($states->get($to_state)->isInitial()) {
                throw new InvalidWorkflowStructure('Trying to transition to initial-state: '.$to_state);
            }

            $state_transitions = $state_transitions->put($transition);
        }

        return $state_transitions;
    }

    /**
     * @param  StateMap $all_states
     * @param  StateTransitions $state_transitions
     * @param  StateInterface $state
     * @param  StateSet $visited_states
     *
     * @return StateSet
     */
    private function depthFirstScan(
        StateMap $all_states,
        StateTransitions $state_transitions,
        StateInterface $state,
        StateSet $visited_states
    ): StateSet {
        if ($visited_states->contains($state)) {
            return $visited_states;
        }
        $visited_states->add($state);

        $child_states = array_map(
            function (TransitionInterface $transition) use ($all_states): StateInterface {
                return $all_states->get($transition->getTo());
            },
            $state_transitions->get($state->getName())->toArray()
        );

        foreach ($child_states as $child_state) {
            $visited_states = $this->depthFirstScan($all_states, $state_transitions, $child_state, $visited_states);
        }

        return $visited_states;
    }

    /**
     * @param  string $state_name
     *
     * @return StateInterface
     */
    private function getStartStateByName(string $state_name)
    {
        $start_state = $this->states->get($state_name);

        if (!$start_state) {
            throw new UnsupportedState("Trying to start statemachine execution at unknown state: ".$state_name);
        }

        if ($start_state->isFinal()) {
            throw new CorruptExecutionFlow("Trying to (re)execute statemachine at final state: ".$state_name);
        }

        return $start_state;
    }

    /**
     * @param  InputInterface $input
     * @param  OutputInterface $output
     *
     * @return StateInterface|null
     */
    private function activateTransition(InputInterface $input, OutputInterface $output)
    {
        $next_state = null;

        foreach ($this->state_transitions->get($output->getCurrentState()) as $transition) {
            if ($transition->isActivatedBy($input, $output)) {
                if ($next_state !== null) {
                    throw new CorruptExecutionFlow(
                        'Trying to activate more than one transition at a time. Transition: '.
                        $output->getCurrentState().' -> '.$next_state->getName().' was activated first. '.
                        'Now '.$transition->getFrom().' -> '.$transition->getTo().' is being activated too.'
                    );
                }
                $next_state = $this->states->get($transition->getTo());
            }
        }

        return $next_state;
    }
}
