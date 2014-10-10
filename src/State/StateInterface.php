<?php

namespace Workflux\State;

use Workflux\StatefulSubjectInterface;

/**
 * StateInterface implementations are expected to act as a nodes that are part of a state graph.
 * The most important methods that allow to expose specific behaviour are "onEntry" and "onExit".
 * Typically a state will manipulate the execution context of a given stateful subject in order to express intent.
 */
interface StateInterface
{
    /**
     * @var string TYPE_INITIAL
     */
    const TYPE_INITIAL = 'initial';

    /**
     * @var string TYPE_ACTIVE
     */
    const TYPE_ACTIVE = 'active';

    /**
     * @var string TYPE_FINAL
     */
    const TYPE_FINAL = 'final';

    /**
     * Returns the state's name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the state's type.
     *
     * @return string One the StateInterface::TYPE_* constant values.
     */
    public function getType();

    /**
     * Tells if a the state is the initial state of the state machine it belongs to.
     *
     * @return bool
     */
    public function isInitial();

    /**
     * Tells if a the state is a active state of the state machine it belongs to.
     *
     * @return bool
     */
    public function isActive();

    /**
     * Tells if a the state is an final state of the state machine it belongs to.
     *
     * @return bool
     */
    public function isFinal();

    /**
     * Runs a specific action when the parent state machine enters this state.
     *
     * @param StatefulSubjectInterface $subject
     */
    public function onEntry(StatefulSubjectInterface $subject);

    /**
     * Runs a specific action when the parent state machine exits this state.
     *
     * @param StatefulSubjectInterface $subject
     */
    public function onExit(StatefulSubjectInterface $subject);
}
