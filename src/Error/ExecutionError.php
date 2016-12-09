<?php

namespace Workflux\Error;

use RuntimeException;
use Workflux\Error\ErrorInterface;

class ExecutionError extends RuntimeException implements ErrorInterface
{

}
