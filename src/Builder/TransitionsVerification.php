<?php

namespace Workflux\Builder;

use Workflux\Error\VerificationError;
use Workflux\StateMachine\StateMachine;

class TransitionsVerification implements VerificationInterface
{
    protected $states;

    protected $transitions;

    public function __construct(array $states, array $transitions)
    {
        $this->states = $states;
        $this->transitions = $transitions;
    }

    public function verify()
    {
        foreach ($this->transitions as $state_name => $state_transitions) {
            if (!isset($this->states[$state_name])) {
                throw new VerificationError(
                    sprintf('Unable to find incoming state "%s" for given transitions. Maybe a typo?', $state_name)
                );
            }

            $this->verifyStateTransitions($state_name, $state_transitions);
        }
    }

    protected function verifyStateTransitions($state_name, array $state_transitions)
    {
        $event_names = array_keys($state_transitions);
        if (count($event_names) > 1
            && in_array(StateMachine::SEQ_TRANSITIONS_KEY, $event_names)
            && count($state_transitions[StateMachine::SEQ_TRANSITIONS_KEY]) > 0
        ) {
            throw new VerificationError(
                sprintf(
                    'Found transitions for both sequential and event based execution' .
                    ', but only one is supported at state "%s".',
                    $state_name
                )
            );
        }

        foreach ($state_transitions as $event_name => $transitions) {
            foreach ($transitions as $transition) {
                $outgoing_state_name = $transition->getOutgoingStateName();
                if (!isset($this->states[$outgoing_state_name])) {
                    throw new VerificationError(
                        sprintf(
                            'Unable to find outgoing state "%s" for transition on event "%s". Maybe a typo?',
                            $outgoing_state_name,
                            $event_name
                        )
                    );
                }
            }
        }
    }
}
