<?php

namespace Workflux\State;

use Workflux\StatefulSubjectInterface;

/**
 * The VariableState class set and removes variables when it transition callbacks (onEntry, onExit) are invoked.
 */
class VariableState extends State
{
    /**
     * @var string OPTION_VARS
     */
    const OPTION_VARS = 'variables';

    /**
     * @var string OPTION_REMOVE_VARS
     */
    const OPTION_REMOVE_VARS = 'remove_variables';

    /**
     * Propagates the new state machine position to the execution context of the given subject,
     * by calling the execution context's "onStateEntry" method.
     *
     * @param StatefulSubjectInterface $subject
     */
    public function onEntry(StatefulSubjectInterface $subject)
    {
        parent::onEntry($subject);

        foreach ($this->getOption(self::OPTION_VARS, []) as $key => $value) {
            $subject->getExecutionContext()->setParameter($key, $value);
        }
    }

    /**
     * Propagates the new state machine position to the execution context of the given subject,
     * by calling the execution context's "onStateExit" method.
     *
     * @param StatefulSubjectInterface $subject
     */
    public function onExit(StatefulSubjectInterface $subject)
    {
        parent::onExit($subject);

        foreach ($this->getOption(self::OPTION_REMOVE_VARS, []) as $key) {
            $subject->getExecutionContext()->removeParameter($key);
        }
    }
}
