<?php

namespace Workflux\Tests;

use Shrink0r\PhpSchema\Factory;
use Shrink0r\PhpSchema\Schema;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Workflux\Error\CorruptExecutionFlow;
use Workflux\Param\Input;
use Workflux\Param\Settings;
use Workflux\StateMachine;
use Workflux\State\FinalState;
use Workflux\State\InitialState;
use Workflux\State\InteractiveState;
use Workflux\State\State;
use Workflux\State\StateSet;
use Workflux\Tests\TestCase;
use Workflux\Transition\ExpressionConstraint;
use Workflux\Transition\Transition;
use Workflux\Transition\TransitionSet;

class StateMachineTest extends TestCase
{
    public function testConstruct()
    {
        $schema = new Schema(
            'default_schema',
            [ 'type' => 'assoc', 'properties' => [ 'is_ready' => [ 'type' => 'bool' ] ] ],
            new Factory
        );

        $states = new StateSet([
            $this->createState('initial', InitialState::CLASS, null, $schema),
            $this->createState('foobar', State::CLASS, null),
            $this->createState('bar', InteractiveState::CLASS, null),
            $this->createState('final', FinalState::CLASS, null)
        ]);

        $transitions = (new TransitionSet)
            ->add(new Transition(
                'initial',
                'foobar',
                new Settings,
                [ new ExpressionConstraint('input.get("is_ready") == true', new ExpressionLanguage) ]
            ))
            ->add(new Transition('foobar', 'bar', new Settings))
            ->add(new Transition('bar', 'final', new Settings));

        $statemachine = new StateMachine('test-machine', $states, $transitions);
        $output = $statemachine->execute(new Input([ 'is_ready' => true ]), 'initial');
        $output = $statemachine->execute(Input::fromOutput($output), $output->getCurrentState());

        $this->assertEquals('final', $output->getCurrentState());
    }

    public function testInfiniteExecutionLoop()
    {
        $this->expectException(CorruptExecutionFlow::CLASS);
        $this->expectExceptionMessage('Trying to execute more than the allowed number of 20 workflow steps.
Looks like there is a loop between: approval -> published -> archive');

        $states = new StateSet([
            $this->createState('initial', InitialState::CLASS),
            $this->createState('edit'),
            $this->createState('approval'),
            $this->createState('published'),
            $this->createState('archive'),
            $this->createState('final', FinalState::CLASS)
        ]);

        $transitions = (new TransitionSet)
            ->add(new Transition('initial', 'edit', new Settings))
            ->add(new Transition('edit', 'approval', new Settings))
            ->add(new Transition('approval', 'published', new Settings))
            ->add(new Transition('published', 'archive', new Settings))
            ->add(new Transition('archive', 'approval', new Settings))
            ->add(new InactiveTransition('archive', 'final', new Settings));

        $statemachine = new StateMachine('test-machine', $states, $transitions);
        $statemachine->execute(new Input, 'initial');
    }
}
