<?php

namespace Workflux\Tests;

use Workflux\Param\Input;
use Workflux\Param\InputInterface;

class InputTest extends TestCase
{
    public function testConstruct()
    {
        $input = new Input([ 'foo' => 'bar' ]);

        $this->assertInstanceOf(InputInterface::CLASS, $input);
    }
}
