<?php

namespace Workflux\Builder;

use Workflux\Error\VerificationError;
use Workflux\State\IState;

class StatesVerification implements IVerification
{
    protected $states;

    protected $transitions;

    protected $initial_state;

    protected $final_states;

    public function __construct(array $states, array $transitions)
    {
        $this->states = $states;
        $this->transitions = $transitions;
    }

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

    protected function verifyState(IState $state)
    {
        $state_name = $state->getName();
        $transition_count = isset($this->transitions[$state_name]) ? count($this->transitions[$state_name]) : 0;

        switch ($state->getType()) {
            case IState::TYPE_INITIAL:
                if ($this->initial_state) {
                    throw new VerificationError(
                        sprintf(
                            'Only one initial state is supported per state machine definition.' .
                            'State "%s" has been previously registered as initial state, so state "%" cant be added.',
                            $this->initial_state->getName(),
                            $state_name
                        )
                    );
                } else {
                    $this->initial_state = $state;
                }
                break;

            case IState::TYPE_FINAL:
                if ($transition_count > 0) {
                    throw new VerificationError(
                        sprintf('State "%s" is final and may not have any transitions.', $state_name)
                    );
                }
                $this->final_states[] = $state;
                break;

            default:
                if ($transition_count === 0) {
                    throw new VerificationError(
                        sprintf(
                            'State "%s" is expected to have at least one transition.' .
                            ' Only "%s" states are permitted to have no transitions.',
                            $state_name,
                            IState::TYPE_FINAL
                        )
                    );
                }
        }
    }
}
