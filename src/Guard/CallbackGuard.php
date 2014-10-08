<?php

namespace Workflux\Guard;

use Workflux\StatefulSubjectInterface;

class CallbackGuard implements GuardInterface
{
    protected $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function accept(StatefulSubjectInterface $subject)
    {
        return call_user_func($this->callback, $subject);
    }

    public function __toString()
    {
        return "\nif callback is true";
    }
}
