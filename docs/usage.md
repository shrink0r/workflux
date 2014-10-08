# Usage

You can create a state machine in code or in configuration files. An xml example is provided at the bottom.

## Building a state machine

```php
<?php

use Workflux\Builder\StateMachineBuilder;
use Workflux\State\State;
use Workflux\Transition\Transition;

$builder = new StateMachineBuilder();
$state_machine = $builder
    ->setStateMachineName(self::MACHINE_NAME)
    ->addStates(
        [
            new State('editing', StateInterface::TYPE_INITIAL),
            new State('approval'),
            new State('published'),
            new State('deleted', StateInterface::TYPE_FINAL)
        ]
    )
    ->addTransition('promote', new Transition('editing', 'approval'))
    ->addTransition('promote', new Transition('approval', 'published'))
    ->addTransition('demote', new Transition([ 'approval', 'published' ], 'editing'))
    ->addTransition('delete', new Transition([ 'editing', 'approval', 'published' ], 'deleted'))
    ->build();

```

## Using a state machine

```php
<?php
// your object that implements StatefulSubjectInterface
$subject = new GenericSubject('test_machine', 'edit');
$target_state = $state_machine->execute($subject, 'promote');

if ($target_state->getName() === $subject->getExecutionContext()->getCurrentStateName()) {
    echo "Yay, it works!";
}
```

## Rendering a state machine

```php
<?php

use Workflux\Renderer\DotGraphRenderer;

$renderer = new DotGraphRenderer();
$dot_code = $renderer->renderGraph($state_machine);

$dot_file_path = sprintf('/your/path/fsm-%s.dot', $state_machine->getName());
$image_file_path = sprintf('/your/path/fsm-%s.svg', $state_machine->getName());;

file_put_contents($dot_file_path, $dot_code);

exec(
    sprintf(
        '/usr/bin/dot -Tsvg  %s -o %s 2>&1',
        escapeshellarg($dot_file_path),
        escapeshellarg($image_file_path)
    ),
    $output,
    $status
);
```

## Declaring a state machine

To load an xml definition into code you may use the `XmlStateMachineBuilder`.

```php
<?php

use Workflux\Builder\XmlStateMachineBuilder;

$state_machine_definition_file = 'state_machine.xml';

$builder = new XmlStateMachineBuilder(
    [
        'state_machine_definition' => $state_machine_definition_file,
        'name' => 'video_transcoding'
    ]
);

$state_machine = $builder->build();
```

The example state machine xml file could look like this:

```xml
<?xml version="1.0" encoding="UTF-8" ?>

<state_machines xmlns="urn:schemas-workflux:statemachine:0.1.0">
    <state_machine name="video_transcoding">

        <initial name="new">
            <event name="promote">
                <transition target="transcoding">
                    <guard class="Workflux\Guard\ExpressionGuard">
                        <option name="expression">params.transcoding_required</option>
                    </guard>
                </transition>
                <transition target="ready">
                    <guard class="Workflux\Guard\ExpressionGuard">
                        <option name="expression">not params.transcoding_required</option>
                    </guard>
                </transition>
            </event>
        </initial>

        <state name="transcoding" class="Workflux\Tests\Parser\Xml\Fixture\CustomState">
            <transition target="ready">
                <guard class="Workflux\Guard\ExpressionGuard">
                    <option name="expression">params.transcoding_success</option>
                </guard>
            </transition>
            <transition target="error">
                <guard class="Workflux\Guard\ExpressionGuard">
                    <option name="expression">not params.retry_limit_reached and not params.transcoding_success</option>
                </guard>
            </transition>
            <transition target="rejected">
                <guard class="Workflux\Guard\ExpressionGuard">
                    <option name="expression">params.retry_limit_reached and not params.transcoding_success</option>
                </guard>
            </transition>
        </state>

        <state name="error">
            <event name="promote">
                <transition target="transcoding" />
            </event>
            <event name="demote">
                <transition target="rejected" />
            </event>
        </state>

        <final name="rejected" />
        <final name="ready" />

    </state_machine>
</state_machines>
```
