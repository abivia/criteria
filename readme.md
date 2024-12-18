# Abivia\Criteria

This is a simple package for evaluating boolean logic in a data structure.

The entry point is `Criteria::evaluate(array $criteria, callable $accessor)`.

`$criteria` is an array of terms. If any of the terms evaluates as true, `evaluate` returns.
This is an implicit "or" operation.

A term is an array with these elements:
* `arg` - the name of a value to be retrieved via the accessor callback.
* `op` - A comparison operator, one of ==, ===, !=, !==, >, >=, <, <=, null, !null, regex, !regex,
in, !in.
If no operator is specified, then == is used.
* `value` or `var` - Required for binary operators (i.e. those other than null, !null), the value
to be compared or the regular expression to be matched. The `value` can be an array.
If an array value is provided for a binary operator, each value is compared 
and the first `true` result causes the expression to be true.
If `var` is provided, it must be a string. This is passed to the accessor callback.
* `name` - An optional name, used when reporting errors.
* `invert` - An optional boolean. When true, the result of the current (sub-)criteria is inverted.
* `and` - An optional list of sub-criteria. The list is evaluated as a series of logical "or"
conjunctions, then a logical "and" is performed against the result of the argument/value operation.
* `xor` - Similar to the `and` element instead the result is exclusive or-ed with the result.

## Examples
Criteria are specified in JSON for readability.
```json
[
  {
    "arg": "language",
    "op": "==",
    "value": "en"
  }
]
```
Will evaluate true if the value of `acessor('language')` is "en".

```json
[
  {
    "arg": "firstName",
    "var": "lastName"
  }
]
```
This assumes the operator is == and Will evaluate true
if  `acessor('firstName')` is equal to `acessor('lastName')`.

```json
[
  {
    "arg": "firstName",
    "op": "==",
    "var": "lastName"
  },
  {
    "arg": "firstName",
    "op": "==",
    "var": ""
  }
]
```
Will evaluate true if  `acessor('firstName')` is equal to `acessor('lastName')` 
or if `acessor('firstName')` is an empty string.

```json
[
  {
    "arg": "province",
    "op": "==",
    "value": "ON",
    "and": [
      {
        "arg": "areaCode",
        "op": "in",
        "value": ["226","249","289","343","365","416","437","519","548","613","647","705","807","905"]
      }
    ]
  }
]
```
Will evaluate true if `acessor('province')` is "ON" 
and `accessor('areaCode')` matches one of the values in the list.

## Operator Access Control

If your application is processing user-generated data nad you want to
restrict access to some operators, this is possible with the 
`operatorState` option.

```php
$criteria = new \Abivia\Criteria\Criteria(
   ['operatorState' => ['regex' => false, '!regex' => false]]
);
```

## Property Configuration

Property names used in the criteria can be changed at instantiation
by passing an array of replacements.
Only the passed in properties will be modified.

```php
$criteria = new \Abivia\Criteria\Criteria(
   ['overrides' => ['arg' => 'left', 'op' => 'operator', 'value' => 'right']]
);
```

Would change a valid input to:
```json
[
  {
    "left": "firstName",
    "operator": "==",
    "right": "lastName",
    "invert": true
  }
]
```
