<?php

namespace Workflux\Error;

use RuntimeException;
use Workflux\Error\ErrorInterface;

class MissingImplementation extends RuntimeException implements ErrorInterface
{

}
