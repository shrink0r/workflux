<?php

namespace Workflux\Guard;

use Workflux\IStatefulSubject;

interface IGuard
{
    public function accept(IStatefulSubject $subject);

    public function __toString();
}
