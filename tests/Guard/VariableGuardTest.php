<?php

namespace Workflux\Tests\Guard;

use Workflux\Error\Error;
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

    public function testGuardThrowsWhenExpressionUsesUndefinedParameter()
    {
        $subject = new GenericSubject('test_machine', 'state1');
        $subject->getExecutionContext()->setParameter('foo', 'bar');
        $subject->getExecutionContext()->setParameter('asdf', 'qwer');

        $expression = 'event.foo === "bar"';
        $guard = new VariableGuard([ 'expression' => $expression ]);

        $this->expectException(Error::CLASS);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            'Expression evaluation failed. Reason: ' .
            'Variable "event" is not valid around position 1 for expression `event.foo === "bar"`.' .
            "\nExpression used: " . $expression .
            "\nExpression vars: subject(object) foo(string) asdf(string) "
        );

        $guard->accept($subject);
    }

    public function testGuardThrowsWhenExpressionAccessedNonObjectParameter()
    {
        $subject = new GenericSubject('test_machine', 'state1');
        $subject->getExecutionContext()->setParameter('foo', 'bar');
        $subject->getExecutionContext()->setParameter('asdf', 'qwer');
        $subject->getExecutionContext()->setParameter('event', null);

        $expression = 'event.foo === "bar"';
        $guard = new VariableGuard([ 'expression' => $expression ]);

        $this->expectException(Error::CLASS);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            'Expression evaluation failed. Reason: Unable to get a property on a non-object.' .
            "\nExpression used: " . $expression .
            "\nExpression vars: subject(object) foo(string) asdf(string) event(NULL) "
        );

        $guard->accept($subject);
    }
}
