<?php

namespace Workflux\Tests\Builder;

use Workflux\Builder\YamlStateMachineBuilder;
use Workflux\Param\Input;
use Workflux\StateMachineInterface;
use Workflux\Tests\TestCase;

class YamlStateMachineBuilderTest extends TestCase
{
    public function testBuild()
    {
        $state_machine = (new YamlStateMachineBuilder(__DIR__.'/Fixture/statemachine.yaml'))
            ->build();
        $rejected_transition = $state_machine->getStateTransitions()->get('transcoding')
            ->filter(function ($transition) {
                return $transition->getTo() === 'rejected';
            })->first();
        $this->assertInstanceOf(StateMachineInterface::CLASS, $state_machine);
        $this->assertTrue($rejected_transition->getSetting('more_stuff'));

        $initial_input = new Input([ 'transcoding_required' => true ]);
        $initial_output = $state_machine->execute($initial_input);
        $current_state = $initial_output->getCurrentState();
        $this->assertEquals('transcoding', $current_state);
        $input = Input::fromOutput($initial_output)->withEvent('video_transcoded');
        $final_output = $state_machine->execute($input, $current_state);
        $this->assertEquals('ready', $final_output->getCurrentState());
    }
}
