<?php

namespace Workflux\Tests;

use Workflux\Input;
use Workflux\InputInterface;

class InputTest extends TestCase
{
    public function testConstruct()
    {
        $input = new Input([ 'foo' => 'bar' ]);

        $this->assertInstanceOf(InputInterface::CLASS, $input);
    }
}
