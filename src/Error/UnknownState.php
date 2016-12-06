<?php

namespace Workflux\Error;

use RuntimeException;
use Workflux\Error\WorkfluxError;

class UnkownState extends RuntimeException implements WorkfluxError
{

}
