<?php

namespace Workflux\Tests\State;

use Workflux\Param\Input;
use Workflux\Param\OutputInterface;
use Workflux\Param\Settings;
use Workflux\State\FinalState;
use Workflux\State\InitialState;
use Workflux\State\InteractiveState;
use Workflux\State\State;
use Workflux\State\ValidatorInterface;
use Workflux\Tests\State\Fixture\StateWithRequiredSettings;
use Workflux\Tests\TestCase;

final class StateTest extends TestCase
{
    public function testExecute()
    {
        $state = $this->createState('foobar');
        $output = $state->execute(new Input([ 'foo' => 'bar' ]));
        $this->assertInstanceOf(OutputInterface::CLASS, $output);
    }

    public function testGetName()
    {
        $state = $this->createState('foobar');
        $this->assertEquals('foobar', $state->getName());
    }

    public function testIsFinal()
    {
        $this->assertFalse($this->createState('foobar')->isFinal());
        $this->assertTrue($this->createState('foobar', FinalState::CLASS)->isFinal());
    }

    public function testIsInitial()
    {
        $this->assertFalse($this->createState('foobar')->isInitial());
        $this->assertTrue($this->createState('foobar', InitialState::CLASS)->isInitial());
    }

    public function testIsInteractive()
    {
        $this->assertFalse($this->createState('foobar')->isInteractive());
        $this->assertTrue($this->createState('foobar', InteractiveState::CLASS)->isInteractive());
    }

    public function testGetValidator()
    {
        $state = $this->createState('foobar');
        $this->assertInstanceOf(ValidatorInterface::CLASS, $state->getValidator());
    }

    public function testGetSettings()
    {
        $state = $this->createState('foobar', State::CLASS, new Settings([ 'foo' => 'bar' ]));
        $this->assertInstanceOf(Settings::CLASS, $state->getSettings());
        $this->assertEquals('bar', $state->getSettings()->get('foo'));
    }

    public function testGetSetting()
    {
        $state = $this->createState('foobar', State::CLASS, new Settings([ 'foo' => 'bar' ]));
        $this->assertEquals('bar', $state->getSetting('foo'));
    }

    /**
     * @expectedException Workflux\Error\ConfigError
     * @expectedExceptionMessage Trying to configure state 'foobar' without required setting 'foobar'.
     */
    public function testMissingRequiredSetting()
    {
        $this->createState('foobar', StateWithRequiredSettings::CLASS);
    } // @codeCoverageIgnore
}
