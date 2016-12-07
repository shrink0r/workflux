<?php

namespace Workflux\Tests\Param;

use Workflux\Param\Input;
use Workflux\Param\InputInterface;
use Workflux\Param\Output;
use Workflux\Tests\TestCase;

final class InputTest extends TestCase
{
    public function testConstruct()
    {
        $this->assertInstanceOf(InputInterface::CLASS, new Input([ 'foo' => 'bar' ]));
    }

    public function testWithEvent()
    {
        $input = new Input([ 'foo' => 'bar' ]);
        $this->assertEmpty($input->getEvent());
        $this->assertFalse($input->hasEvent());

        $input = $input->withEvent('something_happended');
        $this->assertEquals('something_happended', $input->getEvent());
        $this->assertTrue($input->hasEvent());
    }

    public function testFromOutput()
    {
        $input = Input::fromOutput(new Output('some_state', [ 'foo' => 'bar' ]));
        $this->assertEquals('bar', $input->get('foo'));
    }
}
