<?php

namespace Workflux\State;

use Workflux\StatefulSubjectInterface;

interface StateInterface
{
    const TYPE_INITIAL = 'initial';

    const TYPE_ACTIVE = 'active';

    const TYPE_FINAL = 'final';

    public function getName();

    public function getType();

    public function isInitial();

    public function isActive();

    public function isFinal();

    public function onEntry(StatefulSubjectInterface $subject);

    public function onExit(StatefulSubjectInterface $subject);
}
