<?php

namespace Workflux;

use Workflux\Error\CorruptExecutionFlow;
use Workflux\Error\ExecutionError;
use Workflux\Param\Input;
use Workflux\Param\InputInterface;
use Workflux\Param\Output;
use Workflux\Param\OutputInterface;
use Workflux\StateMachineInterface;
use Workflux\State\ExecutionTracker;
use Workflux\State\StateInterface;
use Workflux\State\StateMap;
use Workflux\State\StateSet;
use Workflux\Transition\StateTransitions;
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
     * @param StateSet $state_set
     * @param TransitionSet $transition_set
     */
    public function __construct(string $name, StateSet $state_set, TransitionSet $transition_set)
    {
        list($initial_state, $states, $final_states) = $state_set->splat();
        $this->name = $name;
        $this->states = $states;
        $this->final_states = $final_states;
        $this->initial_state = $initial_state;
        $this->state_transitions = new StateTransitions($states, $transition_set);
    }

    /**
     * @param InputInterface $input
     * @param string $start_state
     *
     * @return OutputInterface
     */
    public function execute(InputInterface $input, string $start_state = null): OutputInterface
    {
        $execution_tracker = new ExecutionTracker($this);
        $next_state = $this->determineStartState($input, $start_state);
        do {
            $cur_cycle = $execution_tracker->track($next_state);
            $output = $next_state->execute($input);
            if ($next_state->isInteractive()) {
                break;
            }
            $next_state = $this->activateTransition($input, $output);
            $input = Input::fromOutput($output);
        } while ($next_state && $cur_cycle < self::MAX_CYCLES);

        if ($next_state && $cur_cycle === self::MAX_CYCLES) {
            throw CorruptExecutionFlow::fromExecutionTracker($execution_tracker, self::MAX_CYCLES);
        }
        return $output;
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
     * @return StateInterface
     */
    private function determineStartState(InputInterface $input, string $state_name = null): StateInterface
    {
        if (!$state_name) {
            return $this->getInitialState();
        }
        if (!$this->states->has($state_name)) {
            throw new ExecutionError("Trying to start statemachine execution at unknown state: ".$state_name);
        }
        $start_state = $this->states->get($state_name);
        if ($start_state->isFinal()) {
            throw new ExecutionError("Trying to (re)execute statemachine at final state: ".$state_name);
        }
        if ($start_state->isInteractive() && !$input->hasEvent()) {
            throw new ExecutionError("Trying to resume statemachine executing without providing an event/signal.");
        }
        return $start_state->isInteractive()
            ? $this->activateTransition($input, Output::fromInput($start_state->getName(), $input))
            : $start_state;
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
                if (is_null($next_state)) {
                    $next_state = $this->states->get($transition->getTo());
                    continue;
                }
                throw new ExecutionError(
                    'Trying to activate more than one transition at a time. Transition: '.
                    $output->getCurrentState().' -> '.$next_state->getName().' was activated first. '.
                    'Now '.$transition->getFrom().' -> '.$transition->getTo().' is being activated too.'
                );
            }
        }
        return $next_state;
    }
}
