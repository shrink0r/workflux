<?php

namespace Workflux\Tests\Builder;

use Workflux\Tests\BaseTestCase;
use Workflux\Error\Error;
use Workflux\Builder\XmlStateMachineBuilder;
use Workflux\Tests\Fixture\GenericSubject;
use Workflux\Renderer\DotGraphRenderer;

class XmlStateMachineBuilderTest extends BaseTestCase
{
    public function testBuild()
    {
        $state_machine_definition_file = dirname(__DIR__) . '/Parser/Xml/Fixture/state_machine.xml';

        $builder = new XmlStateMachineBuilder(
            [ 'state_machine_definition' => $state_machine_definition_file, 'name' => 'video_transcoding' ]
        );

        $builder->build();
    }

    public function testSuccessFlow()
    {
        $builder = new XmlStateMachineBuilder(
            [ 'state_machine_definition' => dirname(__DIR__) . '/Parser/Xml/Fixture/state_machine.xml' ]
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
            [ 'state_machine_definition' => dirname(__DIR__) . '/Parser/Xml/Fixture/state_machine.xml' ]
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
        $this->setExpectedException(
            Error::CLASS,
            'Configured guard classes must implement Workflux\Guard\IGuard.'
        );

        $builder = new XmlStateMachineBuilder(
            [ 'state_machine_definition' => dirname(__DIR__) . '/Builder/Fixture/invalid_guard.xml' ]
        );

        $state_machine = $builder->build();
    }
}
