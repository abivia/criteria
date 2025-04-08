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

    public function testContains()
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"contains","value":"Bob"}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return 'Hello Bob, is anybody home?';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return 'Hello Ted, is anybody home?';
        }));
    }

    public function testContainsArray()
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"contains","value":["Bob","Carol"]}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return 'Hello Bob, is anybody home?';
        }));
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return 'Hello Carol, is anybody home?';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return 'Hello Ted, is anybody home?';
        }));
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

    public function testHasArrayArray()
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"has","value":["0", "1"]}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return ['0'];
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return ['2'];
        }));
    }

    public function testHasArrayScalar()
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"has","value":"0"}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return ['0'];
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return ['2'];
        }));
    }

    public function testHasScalarArray()
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"has","value":["0","3"]}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return '0';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '2';
        }));
    }

    public function testHasScalarScalar()
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"has","value":"0"}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return '0';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '2';
        }));
    }

    public function testInArrayInArray(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"in","value":["0","4","8"]}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return ['4', '8'];
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return ['2', '4'];
        }));
    }

    public function testInArrayInScalar(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"in","value":"4"}]',
            true
        );
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return ['4', '8'];
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return ['2', '4'];
        }));
    }

    public function testInScalarInArray(): void
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

    public function testInScalarInScalar(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"in","value":"4"}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return '4';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '2';
        }));
    }

    public function testIncludesArrayInArray(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"includes","value":["0","4","8"]}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return ['0', '4', '8', '9'];
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return ['4', '8'];
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return ['2', '4'];
        }));
    }

    public function testIncludesArrayInScalar(): void
    {
        // YOU ARE HERE! ******************
        $criteria = json_decode(
            '[{"arg":"prop","op":"includes","value":"4"}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return ['4', '8'];
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return ['2', '6'];
        }));
    }

    public function testIncludesScalarInArray(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"includes","value":["0","4","8"]}]',
            true
        );
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '4';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '2';
        }));
    }

    public function testIncludesScalarInScalar(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"includes","value":"4"}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return '4';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '2';
        }));
    }

    public function testNotContains()
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"!contains","value":"Bob"}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return 'Hello Ted, is anybody home?';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return 'Hello Bob, is anybody home?';
        }));
    }

    public function testNotContainsArray()
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"!contains","value":["Bob","Carol"]}]',
            true
        );
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return 'Hello Bob, is anybody home?';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return 'Hello Carol, is anybody home?';
        }));
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return 'Hello Ted, is anybody home?';
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

    public function testNotEqualsArray(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"!=","value":["4","6"]}]',
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

    public function testNotRegexArray(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"!regex","value":["![0-9]!","!d!"]}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            return 'x';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '4';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return 'd';
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            return '4d';
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
