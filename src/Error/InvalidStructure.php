<?php

namespace Workflux\Error;

use DomainException;
use Workflux\Error\WorkfluxError;

class InvalidStructure extends DomainException implements WorkfluxError
{

}
