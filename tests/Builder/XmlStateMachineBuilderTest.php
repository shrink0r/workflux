<?php

namespace Workflux\Tests\Builder;

use Workflux\Tests\BaseTestCase;
use Workflux\Builder\XmlStateMachineBuilder;

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
}
