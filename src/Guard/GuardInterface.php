<?php

namespace Workflux\Guard;

use Workflux\StatefulSubjectInterface;

/**
 * GuardInterface implementations are supposed to check,
 * if a given subject is acceptable in the context of transitioning from one state to another.
 */
interface GuardInterface
{
    /**
     * Tells if a given stateful subject is acceptable and may transit.
     *
     * @param StatefulSubjectInterface $subject
     *
     * @return boolean
     */
    public function accept(StatefulSubjectInterface $subject);

    /**
     * @return string
     */
    public function __toString();
}
