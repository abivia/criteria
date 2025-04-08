<?php

namespace Abivia\Criteria;

class Criteria
{
    /**
     * @var callable
     */
    private $accessor;
    /**
     * @var array|string[] Operators that need to be inverted when applied to an array.
     */
    private static array $inversions = [
        '!=' => '==',
        '!==' => '===',
        '!contains' => 'contains',
        '!regex' => 'regex',
    ];
    /**
     * @var array
     */
    protected array $operatorEnabled;
    private static array $operators = [
        '==' => 'binary', '!=' => 'binary', '===' => 'binary', '!==' => 'binary',
        '>' => 'binary', '>=' => 'binary', '<' => 'binary', '<=' => 'binary',
        'contains' => 'binary', '!contains'  => 'binary',
        'has' => 'array', '!has' => 'array',
        'in' => 'array', '!in' => 'array',
        'includes' => 'array',  '!includes' => 'array',
        'null' => 'unary', '!null' => 'unary', 'regex' => 'binary', '!regex' => 'binary',
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
     * @param array $options
     * @throws LogicException
     */
    public function __construct(array $options = [])
    {
        if (count($options['overrides'] ?? [])) {
            $this->props = $this->cleanOption(
                'overrides', $options['overrides'], $this->props
            );
        }
        $this->operatorEnabled = array_fill_keys(array_keys(self::$operators), true);
        foreach ($options['operatorState'] ?? [] as $operator => $value) {
            if (isset($this->operatorEnabled[$operator])) {
                $this->operatorEnabled[$operator] = (bool) $value;
            }
        }
    }

    /**
     * @param string $label
     * @param array $updates
     * @param array $reference
     * @param bool $nullable
     * @return array
     * @throws LogicException
     */
    private function cleanOption(
        string $label,
        array $updates,
        array $reference,
        bool $nullable = false
    ): array {
        foreach ($updates as $name => $value) {
            if (!isset($reference[$name])) {
                unset($updates[$name]);
                continue;
            }
            if ($nullable && $value === null) {
                continue;
            }
            if (!is_scalar($value) || (string) $value === '') {
                throw new LogicException(
                    "Error: invalid $label name for \"$name\"."
                );
            }
        }
        $cleaned = array_merge($reference, $updates);
        $duplicate = [];
        foreach ($cleaned as $propName => $newName) {
            if (isset($duplicate[$newName])) {
                throw new LogicException(
                    "Error: duplicate label name. \"$newName\" used for both"
                    . " \"$duplicate[$newName]\" and \"$newName\"."
                );
            }
            $duplicate[$newName] = $propName;
        }
        return $cleaned;
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
            'contains' => str_contains($argument, $value),
            '!contains' => !str_contains($argument, $value),
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
        if ($invert = str_starts_with($operator, '!')) {
            $operator = substr($operator, 1);
        }
        $valueHasOneElement = count($values) === 1;
        if (is_array($argument)) {
            if ($valueHasOneElement) {
                $result = match ($operator) {
                    'in' => false,
                    'has','includes' => in_array($values[0], $argument),
                };
            } else {
                $result = match ($operator) {
                    'has' => count(array_intersect($argument, $values)) !== 0,
                    'in' => count(array_intersect($argument, $values)) === count($argument),
                    'includes' => count(array_intersect($argument, $values)) === count($values),
                };
            }
        } else {
            // The argument is a scalar
            if ($valueHasOneElement) {
                $result = in_array($argument, $values);
            } else {
                $result = match ($operator) {
                    'has','in' => in_array($argument, $values),
                    'includes' => false,
                };
            }
        }
        return $invert ? !$result : $result;
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
        $operator = strtolower($criterion[$this->props['op']] ?? '==');
        if (!isset(self::$operators[$operator]) || !$this->operatorEnabled[$operator]) {
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
            } elseif ($inverseOp =(self::$inversions[$operator] ?? false)) {
                // Array elements are joined by an implicit or, so apply DeMorgan's Theorem
                $result = true;
                foreach ($values as $value) {
                    if ($this->compare($argument, $inverseOp, $value)) {
                        $result = false;
                        break;
                    }
                }
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
