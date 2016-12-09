<?php

namespace Workflux\Error;

use DomainException;
use Workflux\Error\ErrorInterface;

class InvalidStructure extends DomainException implements ErrorInterface
{

}
