<?php

namespace Workflux\Builder;

use Workflux\Error\VerificationError;

class TransitionsVerification implements IVerification
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
}
