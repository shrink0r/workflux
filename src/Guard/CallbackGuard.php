<?php

namespace Workflux\Guard;

use Workflux\IStatefulSubject;

class CallbackGuard implements IGuard
{
    protected $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function accept(IStatefulSubject $subject)
    {
        return call_user_func($this->callback, $subject);
    }
}
