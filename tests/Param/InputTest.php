<?php

namespace Workflux\Tests\Param;

use Workflux\Param\Input;
use Workflux\Param\InputInterface;
use Workflux\Tests\TestCase;

class InputTest extends TestCase
{
    public function testConstruct()
    {
        $input = new Input([ 'foo' => 'bar' ]);

        $this->assertInstanceOf(InputInterface::CLASS, $input);
    }
}
