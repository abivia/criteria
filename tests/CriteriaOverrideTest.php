<?php

use Abivia\Criteria\Criteria;
use Abivia\Criteria\LogicException;
use PHPUnit\Framework\TestCase;

class CriteriaOverrideTest extends TestCase
{
    public Criteria $testObj;

    public function setUp(): void
    {
        $this->testObj = new Criteria([
            'and'=> 'also',
            'bogus' => 'foo',
            'arg' => 'left',
            'name' => 'comment',
            'op' => 'with',
            'value' => 'right',
            'var' => 'from',
            'xor' => 'except'
        ]);
    }

    public function testAndAndXor(): void
    {
        $criteria = json_decode(
            '[{"left":"prop","with":"==","right":"4","also":[],"except":[]}]',
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
            '[{"left":"prop","with":"=*=","right":"4"}]',
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

    public function testBadOverrideBlank(): void
    {
        try {
            new Criteria([
                'name' => '',
            ]);
        } catch (LogicException $ex) {
            $message = $ex->getMessage();
            $this->assertStringStartsWith('Error:', $message);
        }

    }

    public function testBadOverrideDuplicate(): void
    {
        try {
            new Criteria([
                'arg' => 'left',
                'value' => 'left',  // oops!
            ]);
        } catch (LogicException $ex) {
            $message = $ex->getMessage();
            $this->assertStringStartsWith('Error:', $message);
        }

    }

    public function testBadOverrideInvalid(): void
    {
        try {
            new Criteria([
                'arg' => null,
            ]);
        } catch (LogicException $ex) {
            $message = $ex->getMessage();
            $this->assertStringStartsWith('Error:', $message);
        }

    }

    public function testNamedCriterion(): void
    {
        $criteria = json_decode(
            '[{"comment":"bobtest","with":"==","right":"4","from":"prop2"}]',
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
            '[{"left":"prop","with":"==","right":"4","also":[{"with":"foo"}]}]',
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
            '[{"with":"==","right":"4"}]',
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
            '[{"left":"prop","with":"=="}]',
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
            '[{"left":"prop","with":"null"}]',
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
            '[{"left":"prop","with":"==","right":"4"},{"with":"foo"}]',
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
            '[{"left":"prop","with":"==","right":"4","from":"prop2"}]',
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