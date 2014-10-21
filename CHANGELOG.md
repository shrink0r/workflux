# Changelog

All new features, changes and fixes should be listed here. Please use tickets to reference changes.

## 0.5.0 (2014/10/22)

Added an event emitting implementation of the state machine.
Thanks to @MrHash who provided the feedback, that this release is based on.

* [new] #26 Added `Workflux\StateMaching\EventEmittingStateMachine` which will let you hook into the execution of the statemachine via event listeners.
* [chg] #28 State machines may now start with sequential states.
* [chg] #27 The initial state must not be provided within a given execution state when beginning a new execution.
* [chg] Refactored StateMachine and StateMachineBuilder internals.
* [chg] Updated README intro.
* [fix] #27 State transitions are guaranteed to be executed only once.

## 0.4.1 (2014/13/13)

Fixed coding style violations (#24).

## 0.4.0 (2014/10/13)

Added several features such as the VaribaleState and -Guard and fixed some bug. Also the api doc has been completed for `/src'.

* [new] Added `Workflux\State\VariableState` which will automatically set and remove configured execution vars.
* [new] Added `Workflux\Guard\VariableGuard` which provides a shorted way of expressing execution var based transition constraints.
* [chg] Refactored the `Workflux\Parser\Xml\StateMachineDefinitionParser` and extracted `Workflux\Parser\Xml\Xpath` and `Workflux\Parser\Xml\OptionsXpathParser`.
* [chg] Option definitions within xml state definitions are now recursively parsed.
* [chg] Refactored the `Workflux\Builder\(Xml)StateMachineBuilder` classes.
* [fix] Wrong usage examples where fixed within the `usage.md`
* [fix] Xsd schema validation result is now correctly processed.
* [fix] Completed api doc for `/src` files.

## 0.3.0 (2014/10/09)

This basically is a quality assurance release together with a new extra feature.
A lot of code cleaning was done and tests where added for yet uncovered code.

* [new] The `Workflux\State\State` class now supports `Params\Options`
* [fix] The xsd schema-validation result is now actually considered
* [chg] Options can now be nested recursively within xml definitions for states and guards.

## 0.2.0 (2014/10/08)

* [new] Introduced api doc and usage examples.
* [new] Added xsd schema validation for state machine xml declarations.
* [new] You can now configure you own `StateInterface` implementations.

## 0.1.0 (2014/10/07)

Initial version providing a working state machine, with event- and sequential-transitions.
Further more you can define state machines via xml and render them to an image.
