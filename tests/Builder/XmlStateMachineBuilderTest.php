<?php

namespace Workflux\Tests\Builder;

use Workflux\Tests\BaseTestCase;
use Workflux\Builder\XmlStateMachineBuilder;
use Workflux\Tests\Fixture\GenericSubject;
use Workflux\Renderer\DotGraphRenderer;

class XmlStateMachineBuilderTest extends BaseTestCase
{
    public function testBuild()
    {
        $state_machine_definition_file = dirname(__DIR__) . '/Parser/Xml/Fixture/state_machine.xml';

        $builder = new XmlStateMachineBuilder(
            [ 'state_machine_definition' => $state_machine_definition_file ]
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

        $next_state = $state_machine->execute($subject, 'promote');
        $this->assertEquals('transcoding', $next_state->getName());

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
        $this->assertEquals('transcoding', $next_state->getName());

        $next_state = $state_machine->execute($subject, 'demote');
        $this->assertEquals('error', $next_state->getName());

        $next_state = $state_machine->execute($subject, 'promote');
        $this->assertEquals('transcoding', $next_state->getName());

        $subject->getExecutionContext()->setParameter('retry_limit_reached', true);
        $next_state = $state_machine->execute($subject, 'demote');
        $this->assertEquals('rejected', $next_state->getName());
    }
}
