<?php

namespace Workflux\Error;

use RuntimeException;
use Workflux\Error\ErrorInterface;

class UnkownState extends RuntimeException implements ErrorInterface
{

}
