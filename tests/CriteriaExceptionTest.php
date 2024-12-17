<?php

use Abivia\Criteria\Criteria;
use Abivia\Criteria\LogicException;
use PHPUnit\Framework\TestCase;

class CriteriaExceptionTest extends TestCase
{
    public Criteria $testObj;

    public function setUp(): void
    {
        $this->testObj = new Criteria();
    }

    public function testAndAndXor(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"==","value":"4","and":[],"xor":[]}]',
            true
        );
        try {
            $this->testObj->evaluate($criteria, function (string $arg) {
                return '4';
            });
        } catch (LogicException $ex) {
            $message = $ex->getMessage();
            $this->assertStringStartsWith('At 0', $message);
        }
    }

    public function testBadOperator(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"=*=","value":"4"}]',
            true
        );
        try {
            $this->testObj->evaluate($criteria, function (string $arg) {
                return '4';
            });
        } catch (LogicException $ex) {
            $message = $ex->getMessage();
            $this->assertStringStartsWith('At 0', $message);
        }
    }

    public function testNamedCriterion(): void
    {
        $criteria = json_decode(
            '[{"name":"bobtest","op":"==","value":"4","var":"prop2"}]',
            true
        );
        try {
            $this->testObj->evaluate($criteria, function (string $arg) {
                return '4';
            });
        } catch (LogicException $ex) {
            $message = $ex->getMessage();
            $this->assertStringStartsWith('At bobtest', $message);
        }
    }

    public function testNestedCriteriaBad(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"==","value":"4","and":[{"op":"foo"}]}]',
            true
        );
        try {
            $this->testObj->evaluate($criteria, function (string $arg) {
                return '7';
            });
        } catch (LogicException $ex) {
            $message = $ex->getMessage();
            $this->assertStringStartsWith('At 0.0', $message);
        }
    }

    public function testNoArgument(): void
    {
        $criteria = json_decode(
            '[{"op":"==","value":"4"}]',
            true
        );
        try {
            $this->testObj->evaluate($criteria, function (string $arg) {
                return '4';
            });
        } catch (LogicException $ex) {
            $message = $ex->getMessage();
            $this->assertStringStartsWith('At 0', $message);
        }
    }

    public function testNoVarOrValue(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"=="}]',
            true
        );
        try {
            $this->testObj->evaluate($criteria, function (string $arg) {
                return '4';
            });
        } catch (LogicException $ex) {
            $message = $ex->getMessage();
            $this->assertStringStartsWith('At 0', $message);
        }
    }

    public function testNoVarOrValueUnary(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"null"}]',
            true
        );
        $clean = true;
        try {
            $this->testObj->evaluate($criteria, function (string $arg) {
                return '4';
            });
        } catch (LogicException) {
            $clean = false;
        }
        $this->assertTrue($clean);
    }

    public function testSecondCriteriaBad(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"==","value":"4"},{"op":"foo"}]',
            true
        );
        try {
            $this->testObj->evaluate($criteria, function (string $arg) {
                return '7';
            });
        } catch (LogicException $ex) {
            $message = $ex->getMessage();
            $this->assertStringStartsWith('At 1', $message);
        }
    }

    public function testVarAndValue(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"==","value":"4","var":"prop2"}]',
            true
        );
        try {
            $this->testObj->evaluate($criteria, function (string $arg) {
                return '4';
            });
        } catch (LogicException $ex) {
            $message = $ex->getMessage();
            $this->assertStringStartsWith('At 0', $message);
        }
    }

}
