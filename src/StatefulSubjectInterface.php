<?php

namespace Workflux;

interface StatefulSubjectInterface
{
    public function getExecutionContext();
}
