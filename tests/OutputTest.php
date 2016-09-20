<?php

namespace Workflux\Tests;

use Workflux\Input;
use Workflux\Output;
use Workflux\OutputInterface;

class OutputTest extends TestCase
{
    public function testConstruct()
    {
        $this->assertInstanceOf(OutputInterface::CLASS, new Output('initial'));
    }

    public function testHas()
    {
        $output = new Output('initial', [ 'foo' => 'bar' ]);

        $this->assertTrue($output->has('foo'));
        $this->assertFalse($output->has('bar'));
    }

    public function testGet()
    {
        $output = new Output('initial', [ 'foo' => 'bar' ]);

        $this->assertEquals('bar', $output->get('foo'));
        $this->assertNull($output->get('bar'));
    }

    public function testWithParam()
    {
        $output = new Output('initial');
        $output = $output->withParam('foo', 'bar');

        $this->assertTrue($output->has('foo'));
        $this->assertEquals('bar', $output->get('foo'));
    }

    public function testWithParamDeep()
    {
        $output = new Output('initial');
        $output = $output->withParam('foo.0', 'bar');

        $this->assertTrue($output->has('foo'));
        $this->assertEquals([ 'bar' ], $output->get('foo'));
    }

    public function testWithParamFlat()
    {
        $output = new Output('initial');
        $output = $output->withParam('foo.0', 'bar', false);

        $this->assertTrue($output->has('foo.0'));
        $this->assertEquals('bar', $output->get('foo.0', false));
    }

    public function testWithParams()
    {
        $output = new Output('initial');
        $output = $output->withParams([ 'foo' => 'bar', 'msg' => 'hello world' ]);

        $this->assertTrue($output->has('foo'));
        $this->assertTrue($output->has('msg'));
        $this->assertEquals('bar', $output->get('foo'));
        $this->assertEquals('hello world', $output->get('msg'));
    }

    public function testWithoutParam()
    {
        $output = new Output('initial', [ 'foo' => 'bar' ]);
        $output = $output->withoutParam('foo');

        $this->assertFalse($output->has('foo'));
        $this->assertNull($output->get('foo'));
    }

    public function testWithoutParams()
    {
        $output = new Output('initial', [ 'foo' => 'bar', 'msg' => 'hello world' ]);
        $output = $output->withoutParams([ 'foo', 'msg' ]);

        $this->assertFalse($output->has('foo'));
        $this->assertFalse($output->has('msg'));
        $this->assertNull($output->get('foo'));
        $this->assertNull($output->get('msg'));
    }

    public function testToArray()
    {
        $params = [ 'foo' => 'bar', 'msg' => 'hello world' ];
        $output = new Output('initial', $params);

        $this->assertEquals([ 'params' => $params, 'current_state' => 'initial' ], $output->toArray());
    }

    public function testFromInput()
    {
        $params = [ 'foo' => 'bar', 'msg' => 'hello world' ];
        $output = Output::fromInput('initial', new Input($params));

        $this->assertInstanceOf(OutputInterface::CLASS, $output);
        $this->assertEquals([ 'params' => $params, 'current_state' => 'initial' ], $output->toArray());
    }
}
