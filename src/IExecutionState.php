<?php

namespace Workflux;

interface IExecutionState
{
    public function getStateMachineName();

    public function getCurrentStateName();

    public function setParameter($key, $value, $replace = true);

    public function removeParameter($key);

    public function clearParameters();

    public function setParameters($parameters);
}
