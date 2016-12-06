<?php

namespace Workflux\Error;

use RuntimeException;
use Workflux\Error\WorkfluxError;

class ExecutionError extends RuntimeException implements WorkfluxError
{

}
