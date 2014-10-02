<?php

namespace Workflux;

interface IStatefulSubject
{
    public function getStateMachineName();

    public function getCurrentStateName();
}
