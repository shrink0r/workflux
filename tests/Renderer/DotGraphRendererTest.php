<?php

namespace Workflux\Tests\Renderer;

use Workflux\Error\Error;
use Workflux\Tests\BaseTestCase;
use Workflux\Builder\XmlStateMachineBuilder;
use Workflux\Renderer\DotGraphRenderer;

class DotGraphRendererTest extends BaseTestCase
{
    public function testRenderGraph()
    {
        $state_machine_definition_file = __DIR__ . '/Fixture/state_machine.xml';
        $builder = new XmlStateMachineBuilder(
            [ 'state_machine_definition' => $state_machine_definition_file, 'name' => 'video_transcoding' ]
        );
        $state_machine = $builder->build();

        $renderer = new DotGraphRenderer();
        $dot_code = $renderer->renderGraph($state_machine);

        $fixture_file = __DIR__ . '/Fixture/state_machine.dot';
        $expected_code = file_get_contents($fixture_file);

        $this->assertEquals($expected_code, $dot_code);
    }

    public function testInvalidStylesOption()
    {
        $this->expectException(
            Error::CLASS,
            'Encountered unexpected value type for "styles" option.' .
            ' Expected instance of Params\Immutable\ImmutableOptions'
        );
        $state_machine_definition_file = __DIR__ . '/Fixture/state_machine.xml';
        $builder = new XmlStateMachineBuilder(
            [ 'state_machine_definition' => $state_machine_definition_file, 'name' => 'video_transcoding' ]
        );
        $state_machine = $builder->build();

        $renderer = new DotGraphRenderer([ 'style' => 'not what you\'d expected hugh? :P' ]);
        $dot_code = $renderer->renderGraph($state_machine);
    }
}
