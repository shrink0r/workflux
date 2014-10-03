<?php

namespace Workflux\Tests\Guard;

use Workflux\Tests\BaseTestCase;
use Workflux\Guard\ExpressionGuard;
use Workflux\Tests\Fixture\GenericSubject;

class ExpressionGuardTest extends BaseTestCase
{
    public function testGuard()
    {
        $subject = new GenericSubject('test_machine', 'state1');

        $guard = new ExpressionGuard('params.event === "erp.derped"');
        $subject->getExecutionState()->setParameter('event', 'erp.derped');

        $this->assertTrue($guard->accept($subject));
    }
}
