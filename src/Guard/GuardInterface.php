<?php

namespace Workflux\Guard;

use Workflux\StatefulSubjectInterface;

interface GuardInterface
{
    public function accept(StatefulSubjectInterface $subject);

    public function __toString();
}
