<?php

namespace Workflux\Builder;

use Workflux\Error\VerificationError;
use Workflux\State\StateInterface;

/**
 * The StatesVerification is responsable for verifying the states configuration of a state machine setup.
 */
class StatesVerification implements VerificationInterface
{
    /**
     * @var array $states An array of StateInterface
     */
    protected $states;

    /**
     * @var array $transitions
     */
    protected $transitions;

    /**
     * @var StateInterface $initial_state
     */
    protected $initial_state;

    /**
     * @var StateInterface $final_states
     */
    protected $final_states;

    /**
     * @param array $states
     * @param array $transitions
     */
    public function __construct(array $states, array $transitions)
    {
        $this->states = $states;
        $this->transitions = $transitions;
    }

    /**
     * Verifies the given states configuration.
     *
     * @throws VerificationError
     */
    public function verify()
    {
        $this->initial_state = null;
        $this->final_states = [];

        foreach ($this->states as $state_name => $state) {
            $this->verifyState($state);
        }

        if (!$this->initial_state) {
            throw new VerificationError('No state of type "initial" found, but exactly one initial state is required.');
        }

        if (empty($this->final_states)) {
            throw new VerificationError('No state of type "final" found, but at least one final state is required.');
        }
    }

    /**
     * Verifies the given state by employing a state-type specific strategy.
     *
     * @param StateInterface $state
     *
     * @throws VerificationError
     */
    protected function verifyState(StateInterface $state)
    {
        $state_name = $state->getName();
        $transition_count = isset($this->transitions[$state_name]) ? count($this->transitions[$state_name]) : 0;

        switch ($state->getType()) {
            case StateInterface::TYPE_INITIAL:
                $this->verifyInitialState($state);
                break;
            case StateInterface::TYPE_FINAL:
                $this->verifyFinalState($state, $transition_count);
                break;
            default:
                $this->verifyActiveState($state, $transition_count);
        }
    }

    /**
     * Employs an initial-state specific verification.
     *
     * @param StateInterface $state
     *
     * @throws VerificationError
     */
    protected function verifyInitialState(StateInterface $state)
    {
        if ($this->initial_state) {
            throw new VerificationError(
                sprintf(
                    'Only one initial state is supported per state machine definition.' .
                    'State "%s" has been previously registered as initial state, so state "%" cant be added.',
                    $this->initial_state->getName(),
                    $state->getName()
                )
            );
        } else {
            $this->initial_state = $state;
        }
    }

    /**
     * Employs a final-state specific verification.
     *
     * @param StateInterface $state
     * @param int $transition_count Number of transitions attached to the given state.
     *
     * @throws VerificationError
     */
    protected function verifyFinalState(StateInterface $state, $transition_count)
    {
        if ($transition_count > 0) {
            throw new VerificationError(
                sprintf('State "%s" is final and may not have any transitions.', $state->getName())
            );
        }
        $this->final_states[] = $state;
    }

    /**
     * Employs an active-state specific verification.
     *
     * @param StateInterface $state
     * @param int $transition_count Number of transitions attached to the given state.
     *
     * @throws VerificationError
     */
    protected function verifyActiveState(StateInterface $state, $transition_count)
    {
        if ($transition_count === 0) {
            throw new VerificationError(
                sprintf(
                    'State "%s" is expected to have at least one transition.' .
                    ' Only "%s" states are permitted to have no transitions.',
                    $state->getName(),
                    StateInterface::TYPE_FINAL
                )
            );
        }
    }
}
