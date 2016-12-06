<?php

namespace Workflux\Error;

use RuntimeException;
use Workflux\Error\ErrorInterface;

class ConfigError extends RuntimeException implements ErrorInterface
{

}
