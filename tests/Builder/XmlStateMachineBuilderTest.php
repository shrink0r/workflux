<?php

namespace Workflux\Tests\Builder;

use Workflux\Tests\BaseTestCase;
use Workflux\Error\Error;
use Workflux\StateMachine\EventEmittingStateMachine;
use Workflux\Builder\XmlStateMachineBuilder;
use Workflux\Tests\Fixture\GenericSubject;
use Workflux\Renderer\DotGraphRenderer;

class XmlStateMachineBuilderTest extends BaseTestCase
{
    public function testBuild()
    {
        $state_machine_definition_file = __DIR__ . '/Fixture/state_machine.xml';

        $builder = new XmlStateMachineBuilder(
            [ 'state_machine_definition' => $state_machine_definition_file, 'name' => 'video_transcoding' ]
        );

        $state_machine = $builder->build();

        $new_state = $state_machine->getState('rejected');
        $this->assertTrue($new_state->getOption('notify_owner'));
        $this->assertInstanceOf(EventEmittingStateMachine::CLASS, $state_machine);
    }

    public function testSuccessFlow()
    {
        $builder = new XmlStateMachineBuilder(
            [ 'state_machine_definition' => __DIR__ . '/Fixture/state_machine.xml', 'name' => 'video_transcoding' ]
        );

        $state_machine = $builder->build();

        $subject = new GenericSubject('video_transcoding', 'new');
        $subject->getExecutionContext()->setParameter('transcoding_required', true);
        $subject->getExecutionContext()->setParameter('transcoding_success', true);
        $next_state = $state_machine->execute($subject, 'promote');
        $this->assertEquals('ready', $next_state->getName());
    }

    public function testErrorFlow()
    {
        $builder = new XmlStateMachineBuilder(
            [ 'state_machine_definition' => __DIR__ . '/Fixture/state_machine.xml', 'name' => 'video_transcoding' ]
        );

        $state_machine = $builder->build();

        $subject = new GenericSubject('video_transcoding', 'new');
        $subject->getExecutionContext()->setParameter('transcoding_required', true);

        $next_state = $state_machine->execute($subject, 'promote');
        $this->assertEquals('error', $next_state->getName());

        $subject->getExecutionContext()->setParameter('retry_limit_reached', true);
        $next_state = $state_machine->execute($subject, 'promote');
        $this->assertEquals('rejected', $next_state->getName());
    }

    public function testInvalidGuard()
    {
        $this->expectException(
            Error::CLASS,
            'Configured guard classes must implement Workflux\Guard\GuardInterface.'
        );

        $builder = new XmlStateMachineBuilder(
            [ 'state_machine_definition' => __DIR__ . '/Fixture/invalid_guard.xml', 'name' => 'invalid_machine' ]
        );

        $builder->build();
    }

    public function testNonExistantStateMachine()
    {
        $this->expectException(
            Error::CLASS,
            'Unable to find configured state machine with name "not_there".'
        );

        $builder = new XmlStateMachineBuilder(
            [ 'state_machine_definition' => __DIR__ . '/Fixture/state_machine.xml', 'name' => 'not_there' ]
        );

        $builder->build();
    }

    public function testNonExistantStateImplementor()
    {
        $this->expectException(
            Error::CLASS,
            'Unable to load configured custom state implementor "Foo\BarState".'
        );

        $builder = new XmlStateMachineBuilder(
            [
                'state_machine_definition' => __DIR__ . '/Fixture/non_existant_state_implementor.xml',
                'name' => 'invalid_machine'
            ]
        );

        $builder->build();
    }

    public function testInvalidStateImplementor()
    {
        $this->expectException(
            Error::CLASS,
            'Configured custom implementor for state new does not implement "Workflux\State\StateInterface".'
        );

        $builder = new XmlStateMachineBuilder(
            [
                'state_machine_definition' => __DIR__ . '/Fixture/invalid_state_implementor.xml',
                'name' => 'invalid_machine'
            ]
        );

        $builder->build();
    }

    public function testMissingStateMachineName()
    {
        $this->expectException(Error::CLASS, 'Missing required state machine name.');

        $builder = new XmlStateMachineBuilder(
            [ 'state_machine_definition' => __DIR__ . '/Fixture/invalid_state_implementor.xml' ]
        );

        $builder->build();
    }
}
