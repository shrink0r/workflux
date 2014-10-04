<?php

namespace Workflux;

use Workflux\State\IState;

interface IExecutionContext
{
    public function getStateMachineName();

    public function getCurrentStateName();

    public function setParameter($key, $value, $replace = true);

    public function removeParameter($key);

    public function clearParameters();

    public function setParameters($parameters);

    public function onStateEntry(IState $state);

    public function onStateExit(IState $state);
}
