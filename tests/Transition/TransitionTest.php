<?php

namespace Workflux\Tests\Transition;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Workflux\Param\Input;
use Workflux\Param\Output;
use Workflux\Param\Settings;
use Workflux\Tests\TestCase;
use Workflux\Transition\ExpressionConstraint;
use Workflux\Transition\Transition;

final class TransitionTest extends TestCase
{
    public function testIsActivatedBy()
    {
        $transition = new Transition(
            'initial',
            'foo',
            new Settings,
            [ new ExpressionConstraint("input.get('foo') == 'bar'", new ExpressionLanguage) ]
        );
        $this->assertTrue($transition->isActivatedBy(new Input([ 'foo' => 'bar' ]), new Output('state1')));
        $this->assertFalse($transition->isActivatedBy(new Input([ 'foo' => 'snafu' ]), new Output('state1')));
    }

    public function testGetFrom()
    {
        $transition = new Transition('initial', 'foo');
        $this->assertEquals('initial', $transition->getFrom());
    }

    public function testGetTo()
    {
        $transition = new Transition('initial', 'foo');
        $this->assertEquals('foo', $transition->getTo());
    }

    public function testHasConstraints()
    {
        $transition = new Transition('initial', 'foo');
        $this->assertFalse($transition->hasConstraints());
        $transition = new Transition(
            'initial',
            'foo',
            new Settings,
            [ new ExpressionConstraint("input.get('foo') == 'bar'", new ExpressionLanguage) ]
        );
        $this->assertTrue($transition->hasConstraints());
    }

    public function testGetConstraints()
    {
        $transition = new Transition(
            'initial',
            'foo',
            new Settings,
            [ new ExpressionConstraint("input.get('foo') == 'bar'", new ExpressionLanguage) ]
        );
        $this->assertCount(1, $transition->getConstraints());
    }

    public function testGetLabel()
    {
        $transition = new Transition('initial', 'foo', new Settings([ 'foo' => 'bar' ]));
        $this->assertEmpty($transition->getLabel());
        $transition = new Transition('initial', 'foo', new Settings([ 'label' => 'hello world!' ]));
        $this->assertEquals('hello world!', $transition->getLabel());
    }

    public function testGetSettings()
    {
        $transition = new Transition('initial', 'foo', new Settings([ 'foo' => 'bar' ]));
        $this->assertEquals('bar', $transition->getSettings()->get('foo'));
    }

    public function testGetSetting()
    {
        $transition = new Transition('initial', 'foo', new Settings([ 'foo' => 'bar' ]));
        $this->assertEquals('bar', $transition->getSetting('foo'));
        $this->assertNull($transition->getSetting('bar'));
    }

    public function testHasSetting()
    {
        $transition = new Transition('initial', 'foo', new Settings([ 'foo' => 'bar' ]));
        $this->assertTrue($transition->hasSetting('foo'));
        $this->assertFalse($transition->hasSetting('bar'));
    }
}
