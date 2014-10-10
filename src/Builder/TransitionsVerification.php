<?php

namespace Workflux\Builder;

use Workflux\Error\VerificationError;
use Workflux\StateMachine\StateMachine;

/**
 * The TransitionsVerification is responsable for making sure that a given transition configuration is valid.
 */
class TransitionsVerification implements VerificationInterface
{
    /**
     * @var array $states
     */
    protected $states;

    /**
     * @var array $transitions
     */
    protected $transitions;

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
     * Verifies that the defined transitions correctly connect all states.
     *
     * @throws VerificationError
     */
    public function verify()
    {
        foreach ($this->transitions as $state_name => $state_transitions) {
            if (!isset($this->states[$state_name])) {
                throw new VerificationError(
                    sprintf('Unable to find incoming state "%s" for given transitions. Maybe a typo?', $state_name)
                );
            }

            $this->verifyBehaviouralType($state_name, $state_transitions);
            $this->verifyStateTransitions($state_name, $state_transitions);
        }
    }

    /**
     * Verifies that transitions for a specific state are correctly connection.
     *
     * @param string state_name
     * @param array $state_transitions
     *
     * @throws VerificationError
     */
    protected function verifyStateTransitions($state_name, array $state_transitions)
    {
        foreach ($state_transitions as $event_name => $transitions) {
            foreach ($transitions as $transition) {
                $outgoing_state_name = $transition->getOutgoingStateName();
                if (!isset($this->states[$outgoing_state_name])) {
                    throw new VerificationError(
                        sprintf(
                            'Unable to find outgoing state for transition "%s -> %s" and event "%s". Maybe a typo?',
                            $state_name,
                            $outgoing_state_name,
                            $event_name
                        )
                    );
                }
            }
        }
    }

    /**
     * Verifies that a given state only has either sequential or event transitions.
     *
     * @param string $state_name
     * @param array $state_transitions
     *
     * @throws VerificationError
     */
    protected function verifyBehaviouralType($state_name, array $state_transitions)
    {
        $event_names = array_keys($state_transitions);
        if (count($event_names) > 1
            && in_array(StateMachine::SEQ_TRANSITIONS_KEY, $event_names)
            && count($state_transitions[StateMachine::SEQ_TRANSITIONS_KEY]) > 0
        ) {
            throw new VerificationError(
                sprintf(
                    'Found transitions for both sequential and event based execution.' .
                    ' State "%s" may  behave as an event-node or a sequential node, but not both at once.',
                    $state_name
                )
            );
        }
    }
}
