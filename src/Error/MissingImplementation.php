<?php

namespace Workflux\Error;

use RuntimeException;
use Workflux\Error\WorkfluxError;

class MissingImplementation extends RuntimeException implements WorkfluxError
{

}
