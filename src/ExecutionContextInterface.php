<?php

namespace Workflux;

use Workflux\State\StateInterface;

interface ExecutionContextInterface
{
    public function getStateMachineName();

    public function getCurrentStateName();

    public function hasParameter($key);

    public function getParameter($key, $default = null);

    public function setParameter($key, $value, $replace = true);

    public function removeParameter($key);

    public function clearParameters();

    public function setParameters($parameters);

    public function onStateEntry(StateInterface $state);

    public function onStateExit(StateInterface $state);
}
