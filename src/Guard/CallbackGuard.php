<?php

namespace Workflux\Guard;

use Workflux\StatefulSubjectInterface;

/**
 * The CallbackGuard employs it's verification strategy by simply delegating the verification to a given callback.
 */
class CallbackGuard implements GuardInterface
{
    /**
     * @var callable $callback
     */
    protected $callback;

    /**
     * Creates a new CallbackGuard instance.
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Invokes the configured callback on a given stateful subject and returns the result.
     *
     * @param StatefulSubjectInterface $subject
     *
     * @return boolean
     */
    public function accept(StatefulSubjectInterface $subject)
    {
        return call_user_func($this->callback, $subject);
    }

    /**
     * Returns a string represenation of the guard.
     *
     * @return string
     */
    public function __toString()
    {
        return "\nif callback is true";
    }
}
