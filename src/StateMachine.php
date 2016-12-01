<?php

namespace Workflux;

use Ds\Map;
use Ds\Vector;
use Shrink0r\SuffixTree\Builder\SuffixTreeBuilder;
use Workflux\Error\CorruptExecutionFlow;
use Workflux\Error\InvalidStructure;
use Workflux\Error\LogicError;
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
    const MAX_CYCLES = 20;

    /**
     * @var string $name
     */
    private $name;

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
     * @param string $name
     * @param StateSet $states
     * @param TransitionSet $transitions
     */
    public function __construct(string $name, StateSet $states, TransitionSet $transitions)
    {
        list($initial_state, $all_states, $final_states) = $states->splat();
        $state_transitions = StateTransitions::create($all_states, $transitions);
        $reachable_states = $this->depthFirstScan($all_states, $state_transitions, $initial_state, new StateSet);
        if (count($reachable_states) !== count($all_states)) {
            throw new InvalidStructure('Not all states are properly connected.');
        }
        $this->name = $name;
        $this->initial_state = $initial_state;
        $this->states = $all_states;
        $this->final_states = $final_states;
        $this->state_transitions = $state_transitions;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
     * @param InputInterface $input
     * @param string $state_name
     *
     * @return OutputInterface
     */
    public function execute(InputInterface $input, string $state_name): OutputInterface
    {
        $bread_crumbs = new Vector;
        $execution_cnt_map = new Map;
        foreach ($this->getStates() as $state) {
            $execution_cnt_map[$state->getName()] = 0;
        }
        $next_state = $this->getStartStateByName($state_name);
        do {
            $bread_crumbs->push($next_state->getName());
            $execution_cnt_map[$next_state->getName()]++;
            $output = $next_state->execute($input);
            $next_state = $this->activateTransition($input, $output);
            $input = Input::fromOutput($output);
        } while ($next_state
            && !$next_state->isInteractive($input)
            && $execution_cnt_map[$next_state->getName()] < self::MAX_CYCLES
        );
        if ($next_state && $execution_cnt_map[$next_state->getName()] === self::MAX_CYCLES) {
            throw CorruptExecutionFlow::raiseLoopDetected($bread_crumbs, self::MAX_CYCLES);
        }
        return $next_state ? $output->withCurrentState($next_state->getName()) : $output;
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
        StateMap $states,
        StateTransitions $transitions,
        StateInterface $state,
        StateSet $visited_states
    ): StateSet {
        if ($visited_states->contains($state)) {
            return $visited_states;
        }
        $visited_states->add($state);
        $child_states = array_map(
            function (TransitionInterface $transition) use ($states): StateInterface {
                return $states->get($transition->getTo());
            },
            $transitions->get($state->getName())->toArray()
        );
        foreach ($child_states as $child_state) {
            $visited_states = $this->depthFirstScan($states, $transitions, $child_state, $visited_states);
        }
        return $visited_states;
    }

    /**
     * @param  string $state_name
     *
     * @return StateInterface
     */
    private function getStartStateByName(string $state_name): StateInterface
    {
        $start_state = $this->states->get($state_name);
        if (!$start_state) {
            throw new UnsupportedState("Trying to start statemachine execution at unknown state: ".$state_name);
        }
        if ($start_state->isFinal()) {
            throw new LogicError("Trying to (re)execute statemachine at final state: ".$state_name);
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
                    throw new LogicError(
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
