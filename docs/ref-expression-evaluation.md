# Reference: Expression Evaluation Logic

This document provides the canonical implementation for the `evaluate()` methods in the `UnaryExpression` and `FunctionExpression` classes.

---

## `UnaryExpression::evaluate()`

A Unary Expression operates on a single value (the operand). This method gets the operand's value and applies a single operator to it.

This code belongs in `src/Contracts/Expressions/UnaryExpression.php`.

```php
public function evaluate(PolicyContext $context, AttributeAccessor $accessor): bool
{
    // 1. Get the actual value of the operand from the context.
    $value = $this->operand->getValue($context, $accessor);

    // 2. Apply the operator to the value.
    return match ($this->operator) {
        '!', 'not' => !$value,
        'empty' => empty($value),
        'not-empty' => !empty($value),
        'is-null' => is_null($value),
        'not-null' => !is_null($value),
        default => false, // Unknown operator
    };
}
```

---

## `FunctionExpression::evaluate()`

A Function Expression evaluates a whitelisted, built-in function using a dynamic set of arguments. For security, a `match` statement is used to explicitly define which functions can be called, preventing the policy from executing arbitrary PHP code.

This code belongs in `src/Contracts/Expressions/FunctionExpression.php`.

```php
public function evaluate(PolicyContext $context, AttributeAccessor $accessor): bool
{
    // 1. Resolve all arguments.
    // The arguments can be Attribute objects or literal values.
    // This loop converts all of them into simple, resolved values.
    $resolvedArgs = [];
    foreach ($this->arguments as $arg) {
        if ($arg instanceof Attribute) {
            $resolvedArgs[] = $arg->getValue($context, $accessor);
        } else {
            // The argument is already a literal value.
            $resolvedArgs[] = $arg;
        }
    }

    // 2. Match the function name to a whitelisted function and execute it.
    return match ($this->functionName) {
        'startsWith' =>
            // Ensure we have the right number of arguments before calling.
            count($resolvedArgs) === 2 && is_string($resolvedArgs[0]) && is_string($resolvedArgs[1])
            ? str_starts_with($resolvedArgs[0], $resolvedArgs[1])
            : false,

        'endsWith' =>
            count($resolvedArgs) === 2 && is_string($resolvedArgs[0]) && is_string($resolvedArgs[1])
            ? str_ends_with($resolvedArgs[0], $resolvedArgs[1])
            : false,

        'contains' =>
            count($resolvedArgs) === 2
            ? (is_array($resolvedArgs[0])
                ? in_array($resolvedArgs[1], $resolvedArgs[0])
                : (is_string($resolvedArgs[0]) ? str_contains($resolvedArgs[0], $resolvedArgs[1]) : false)
              )
            : false,

        'isBetween' =>
            count($resolvedArgs) === 3 && is_numeric($resolvedArgs[0])
            ? ($resolvedArgs[0] >= $resolvedArgs[1] && $resolvedArgs[0] <= $resolvedArgs[2])
            : false,

        default => false, // Function not found in whitelist.
    };
}
```
