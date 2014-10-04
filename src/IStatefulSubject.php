<?php

namespace Workflux;

interface IStatefulSubject
{
    public function getExecutionContext();
}
