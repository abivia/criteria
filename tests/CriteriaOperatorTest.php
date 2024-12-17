<?php

use Abivia\Criteria\Criteria;
use PHPUnit\Framework\TestCase;

class CriteriaOperatorTest extends TestCase
{
    public Criteria $testObj;

    public function setUp(): void
    {
        $this->testObj = new Criteria();
    }

    public function testEqualsValue(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"==","value":"4"}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return '4';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '2';
        }));
    }

    public function testEqualsDefaultOperator(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","value":"4"}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return '4';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '2';
        }));
    }

    public function testEqualsArrayValue(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"==","value":["7","4"]}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return '4';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '2';
        }));
    }

    public function testEqualsVar(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"==","var":"prop2"}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            $data = ['prop' => 4, 'prop2' => '4'];
            return $data[$arg];
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            $data = ['prop' => 4, 'prop2' => '2'];
            return $data[$arg];
        }));
    }

    public function testGreaterThan(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":">","value":"4"}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return '6';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '2';
        }));
    }

    public function testGreaterThanOrEqual(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":">=","value":"4"}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return '6';
        }));
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return '4';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '2';
        }));
    }

    public function testIdentical(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"===","value":4}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return 4;
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '4';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '2';
        }));
    }

    public function testInvert(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"==","value":"4","invert":true}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return '2';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '4';
        }));
    }

    public function testLessThan(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"<","value":"4"}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return '2';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '6';
        }));
    }

    public function testLessThanOrEqual(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"<=","value":"4"}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return '2';
        }));
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return '4';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '6';
        }));
    }

    public function testIn(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"in","value":["0","4","8"]}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return '4';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '2';
        }));
    }

    public function testNotEquals(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"!=","value":"4"}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return '2';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '4';
        }));
    }

    public function testNotIdentical(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"!==","value":4}]',
            true
        );
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return 4;
        }));
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return '4';
        }));
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return '2';
        }));
    }

    public function testNotIn(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"!in","value":["0","4","8"]}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return '2';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '4';
        }));
    }

    public function testNotNull(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"!null"}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return '4';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return null;
        }));
    }

    public function testNotRegex(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"!regex","value":"![0-9]!"}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return 'x';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '4';
        }));
    }

    public function testNull(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"null"}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return null;
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '6';
        }));
    }

    public function testRegex(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"regex","value":"![0-9]!"}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return '4';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return 'x';
        }));
    }

}
