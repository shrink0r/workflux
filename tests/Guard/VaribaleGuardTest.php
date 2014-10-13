<?php

namespace Workflux\Tests\Guard;

use Workflux\Tests\BaseTestCase;
use Workflux\Guard\VariableGuard;
use Workflux\Tests\Fixture\GenericSubject;

class VariableGuardTest extends BaseTestCase
{
    public function testGuard()
    {
        $subject = new GenericSubject('test_machine', 'state1');

        $guard = new VariableGuard([ 'expression' => 'event === "erp.derped"' ]);
        $subject->getExecutionContext()->setParameter('event', 'erp.derped');

        $this->assertTrue($guard->accept($subject));
        $this->assertEquals(PHP_EOL . 'if event === "erp.derped"', $guard->__toString());
    }
}
