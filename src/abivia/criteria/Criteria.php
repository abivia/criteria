<?php

namespace Abivia\Criteria;

class Criteria
{
    /**
     * @var callable
     */
    private $accessor;
    private static array $operators = [
        '==' => 'binary', '!=' => 'binary', '===' => 'binary', '!==' => 'binary',
        '>' => 'binary', '>=' => 'binary', '<' => 'binary', '<=' => 'binary',
        'null' => 'unary', '!null' => 'unary', 'regex' => 'binary', '!regex' => 'binary',
        'in' => 'array', '!in' => 'array',
    ];
    private array $props = [
        'and' => 'and',
        'arg' => 'arg',
        'invert' => 'invert',
        'name' => 'name',
        'op' => 'op',
        'value' => 'value',
        'var' => 'var',
        'xor' => 'xor'
    ];
    private array $stack;

    /**
     * Create a Criteria object, optionally overriding properties.
     * @param array $overrides
     * @throws LogicException
     */
    public function __construct(array $overrides = [])
    {
        if (count($overrides)) {
            foreach ($overrides as $name => $value) {
                if (!isset($this->props[$name])) {
                    unset($overrides[$name]);
                    continue;
                }
                if (!is_scalar($value) || (string) $value === '') {
                    throw new LogicException(
                        "Error: invalid property name for \"$name\"."
                    );
                }
            }
            $props = array_merge($this->props, $overrides);
            $duplicate = [];
            foreach ($props as $propName => $newName) {
                if (isset($duplicate[$newName])) {
                    throw new LogicException(
                        "Error: duplicate property name. \"$newName\" used for both"
                        . " \"$duplicate[$newName]\" and \"$newName\"."
                    );
                }
                $duplicate[$newName] = $propName;
            }
            $this->props = $props;
        }
    }

    /**
     * Perform scalar operations.
     * @param mixed $argument
     * @param string $operator
     * @param mixed|null $value
     * @return bool
     */
    private function compare(
        mixed $argument, string $operator, mixed $value = null
    ): bool {
        return match ($operator) {
            '==' => $argument == $value,
            '!=' => $argument != $value,
            '===' => $argument === $value,
            '!==' => $argument !== $value,
            '>' => $argument > $value,
            '>=' => $argument >= $value,
            '<' => $argument < $value,
            '<=' => $argument <= $value,
            'null' => $argument === null,
            '!null' => $argument !== null,
            'regex' => preg_match($value, $argument),
            '!regex' => !preg_match($value, $argument),
        };
    }

    /**
     * Perform the array-based operations.
     * @param $argument
     * @param string $operator
     * @param array $values
     * @return bool
     */
    private function compareArray($argument, string $operator, array $values): bool
    {
        return match ($operator) {
            'in' => in_array($argument, $values),
            '!in' => !in_array($argument, $values),
        };
    }

    /**
     * Evaluate a set of criteria.
     * @param array $criteria
     * @param callable $accessor
     * @return bool
     * @throws LogicException
     */
    public function evaluate(array $criteria, callable $accessor): bool
    {
        $this->accessor = $accessor;
        $this->stack = [];
        $result = false;
        // Elements at the top level are implicitly connected with the or operator.
        foreach ($criteria as $key => $criterion) {
            $this->stack = [$criterion[$this->props['name']] ?? $key];
            if ($this->test($criterion)) {
                $result = true;
                break;
            }

        }
        return $result;
    }

    /**
     * Add the criteria path and throw a logic exception.
     * @param string $message
     * @return void
     * @throws LogicException
     */
    private function exception(string $message): void
    {
        throw new LogicException(
            "At " . implode('.', $this->stack) . ": $message"
        );
    }

    /**
     * Evaluate a criterion.
     * @param array $criterion
     * @return bool
     * @throws LogicException
     */
    private function test(array $criterion): bool
    {
        if (!isset($criterion[$this->props['arg']])) {
            $this->exception("Property {$this->props['arg']} missing from criterion.");
        }
        $argument = ($this->accessor)($criterion[$this->props['arg']]);
        $operator = strtolower($criterion[$this->props['op']] ?? '[none]');
        if (!isset(self::$operators[$operator])) {
            $this->exception("Unrecognized operator \"$operator\".");
        }
        $mode = self::$operators[$operator];
        $val = isset($criterion[$this->props['value']]);
        $var = isset($criterion[$this->props['var']]);
        if ($val && $var) {
            $this->exception(
                "Criterion can't have both {$this->props['value']}"
                . " and {$this->props['var']} properties."
            );
        }
        if ($mode !== 'unary' && !($val || $var)) {
            $this->exception(
                "Criterion has no {$this->props['value']}"
                . " or {$this->props['var']} property."
            );
        }
        if ($mode === 'unary') {
            $result = $this->compare($argument, $operator);
        } else {
            if ($var) {
                $values = ($this->accessor)($criterion[$this->props['var']]);
            } else {
                $values = $criterion[$this->props['value']];
            }
            $values = is_array($values) ? $values : [$values];
            if ($mode === 'array') {
                $result = $this->compareArray($argument, $operator, $values);
            } else {
                $result = false;
                foreach ($values as $value) {
                    if ($this->compare($argument, $operator, $value)) {
                        $result = true;
                        break;
                    }
                }
            }
        }
        $and = isset($criterion[$this->props['and']]);
        $xor = isset($criterion[$this->props['xor']]);
        if ($and && $xor) {
            $this->exception(
                "Criterion has both \"{$this->props['and']}\""
                . " and \"{$this->props['xor']}\" clauses."
            );
        }
        if ($and || $xor) {
            $conjunction = $and ? $this->props['and'] : $this->props['xor'];
            $pass = false;
            // Treat sub-criteria as an "or" list.
            foreach ($criterion[$conjunction] as $key => $subCriterion) {
                $this->stack[] = $subCriterion[$this->props['name']] ?? $key;
                $pass = $this->test($subCriterion);
                array_pop($this->stack);
                if ($pass) {
                    break;
                }
            }
            if ($and) {
                $result &= $pass;
            } else {
                $result = $result !== $pass;
            }
        }
        return ($criterion[$this->props['invert']] ?? false) ? !$result : $result;
    }

}
