<?php

namespace Workflux\Tests\Param;

use Workflux\Param\Settings;
use Workflux\Param\ParamHolderInterface;
use Workflux\Tests\TestCase;

final class SettingsTest extends TestCase
{
    public function testConstruct()
    {
        $this->assertInstanceOf(ParamHolderInterface::CLASS, new Settings);
    }

    public function testHas()
    {
        $settings = new Settings([ 'foo' => 'bar' ]);
        $this->assertTrue($settings->has('foo'));
        $this->assertFalse($settings->has('bar'));
    }

    public function testGet()
    {
        $settings = new Settings([ 'foo' => 'bar' ]);
        $this->assertEquals('bar', $settings->get('foo'));
        $this->assertNull($settings->get('bar'));
    }

    public function testWithParam()
    {
        $settings = (new Settings)->withParam('foo', 'bar');
        $this->assertTrue($settings->has('foo'));
        $this->assertEquals('bar', $settings->get('foo'));
    }

    public function testWithParamDeep()
    {
        $settings = (new Settings)->withParam('foo.0', 'bar');
        $this->assertTrue($settings->has('foo'));
        $this->assertEquals([ 'bar' ], $settings->get('foo'));
    }

    public function testWithParamFlat()
    {
        $settings = (new Settings)->withParam('foo.0', 'bar', false);
        $this->assertTrue($settings->has('foo.0'));
        $this->assertEquals('bar', $settings->get('foo.0', false));
    }

    public function testWithParams()
    {
        $settings = (new Settings)->withParams([ 'foo' => 'bar', 'msg' => 'hello world' ]);
        $this->assertTrue($settings->has('foo'));
        $this->assertTrue($settings->has('msg'));
        $this->assertEquals('bar', $settings->get('foo'));
        $this->assertEquals('hello world', $settings->get('msg'));
    }

    public function testWithoutParam()
    {
        $settings = (new Settings([ 'foo' => 'bar' ]))->withoutParam('foo');
        $this->assertFalse($settings->has('foo'));
        $this->assertNull($settings->get('foo'));
    }

    public function testWithoutNonExistantParam()
    {
        $settings = (new Settings([ 'foo' => 'bar' ]))->withoutParam('barfoo');
        $this->assertTrue($settings->has('foo'));
        $this->assertEquals('bar', $settings->get('foo'));
    }

    public function testInvalidPath()
    {
        $settings = new Settings([ 'foo' => [ 'bar' => [ 'foobar' => 'baz' ] ] ]);
        $this->assertEquals('baz', $settings->get('foo.bar.foobar'));
        $this->assertNull($settings->get('foo.baz.foobar'));
    }

    public function testWithoutParams()
    {
        $settings = (new Settings([ 'foo' => 'bar', 'msg' => 'hello world' ]))->withoutParams([ 'foo', 'msg' ]);
        $this->assertFalse($settings->has('foo'));
        $this->assertFalse($settings->has('msg'));
        $this->assertNull($settings->get('foo'));
        $this->assertNull($settings->get('msg'));
    }

    public function testToArray()
    {
        $params = [ 'foo' => 'bar', 'msg' => 'hello world' ];
        $this->assertEquals($params, (new Settings($params))->toArray());
    }
}
