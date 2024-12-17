<?php

use Abivia\Criteria\Criteria;
use PHPUnit\Framework\TestCase;

class CriteriaConjunctionTest extends TestCase
{
    public Criteria $testObj;

    public function setUp(): void
    {
        $this->testObj = new Criteria();
    }

    public function testAnd(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"==","value":"4",'
            . '"and":[{"arg":"prop2","op":"==","value":"2"}]}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            $data = ['prop' => '4', 'prop2' => '2'];
            return $data[$arg];
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            $data = ['prop' => '4', 'prop2' => '9'];
            return $data[$arg];
        }));
    }

    public function testXor(): void
    {
        $criteria = json_decode(
            '[{"arg":"prop","op":"==","value":"4",'
            . '"xor":[{"arg":"prop2","op":"==","value":"2"}]}]',
            true
        );
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            $data = ['prop' => '4', 'prop2' => '9'];
            return $data[$arg];
        }));
        $this->assertTrue($this->testObj->evaluate($criteria, function (string $arg) {
            $data = ['prop' => 'x', 'prop2' => '2'];
            return $data[$arg];
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            $data = ['prop' => '4', 'prop2' => '2'];
            return $data[$arg];
        }));
        $this->assertFalse($this->testObj->evaluate($criteria, function (string $arg) {
            $data = ['prop' => 'a', 'prop2' => 'b'];
            return $data[$arg];
        }));
    }

}
