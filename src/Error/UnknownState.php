<?php

namespace Workflux\Error;

use RuntimeException;
use Workflux\Error\ErrorInterface;

class UnknownState extends RuntimeException implements ErrorInterface
{

}
