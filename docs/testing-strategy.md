# Testing Strategy & Workflow

This document outlines the concepts, setup, and strategy for writing automated tests for the ABAC-Auth library using PHPUnit.

## 1. Core Concepts of Unit Testing

### What is Unit Testing?
**Unit Testing** is the practice of testing the smallest possible "units" of your code—typically a single method within a class—in complete isolation from the rest of the application. The goal is to prove that a single method behaves exactly as expected given a specific input.

### Why is it Essential?
*   **Safety Net:** A good test suite allows you to refactor code or add new features with confidence. If you accidentally break something, a test will fail immediately, telling you exactly what went wrong.
*   **Bug Prevention:** It helps you find and fix bugs early, when they are cheapest and easiest to fix.
*   **Living Documentation:** A well-written test is a perfect piece of documentation. It clearly shows what a method is expected to do and how it handles edge cases.
*   **Better Design:** Writing testable code often forces you to design better code (e.g., using Dependency Injection).

### The "Arrange, Act, Assert" Pattern
Every unit test follows this simple, three-step pattern:

1.  **Arrange:** Set up the world for your test. Create the object you're going to test and prepare any inputs or "mock" dependencies it needs.
2.  **Act:** Execute the single public method you are testing. This should ideally be just one line of code.
3.  **Assert:** Check that the result of the "Act" step is what you expected. PHPUnit provides assertion methods like `assertTrue()`, `assertEquals()`, etc.

## 2. Setting Up the Testing Environment

### PHPUnit Configuration (`phpunit.xml`)
You need a configuration file in the root of your project to tell PHPUnit how to run your tests.

**File: `phpunit.xml`**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.5/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         cacheDirectory=".phpunit.cache">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>src</directory>
        </include>
    </source>
</phpunit>
```

### Running the Test Suite
With the `phpunit.xml` file in place, you can run all tests from your project's root directory with a single command:
```shell
./vendor/bin/phpunit
```

To run only a specific suite (e.g., only Unit tests):
```shell
./vendor/bin/phpunit --testsuite Unit
```

## 3. Test Suite Organization

A well-organized `tests/` directory is crucial for a maintainable project.

### Directory Structure
The best practice is to have your `tests/` directory **mirror your `src/` directory structure**, while also separating tests by type (Unit, Integration).

**Recommended `tests/` structure:**
```
tests/
├── Unit/
│   ├── Adapters/
│   ├── Contracts/
│   │   └── Expressions/
│   │       └── UnaryExpressionTest.php
│   ├── Foundation/
│   └── Support/
├── Integration/
│   └── ...
├── Stubs/
│   ├── Item.php
│   ├── Post.php
│   └── User.php
└── Data/
    └── policies.json
```
*   **`tests/Unit/`**: Contains tests for individual classes in isolation. The structure inside mirrors `src/`.
*   **`tests/Integration/`**: Contains tests for how two or more components work together.
*   **`tests/Stubs/`**: Contains fake classes (`User`, `Post`) used only for testing purposes.
*   **`tests/Data/`**: Contains test data files, like `policies.json`.

### Autoloading Test Files
To ensure your test stubs are loaded correctly, add them to the `autoload-dev` section of your `composer.json` file.

**File: `composer.json`**
```json
"autoload-dev": {
    "psr-4": {
        "mnaatjes\\ABAC\\Tests\\": "tests/",
        "mnaatjes\\ABAC\\Tests\\Stubs\\": "tests/Stubs/"
    }
},
```
After changing this, run `composer dump-autoload` to update the autoloader.

## 4. Writing Your First Test

### Understanding `TestCase`
`PHPUnit\Framework\TestCase` is the fundamental base class that all PHPUnit tests must extend. Think of it as a toolkit. By writing `class MyTest extends TestCase`, your class inherits:
*   **The Assertion Library**: All the `$this->assert...()` methods (`assertTrue`, `assertEquals`, etc.).
*   **Lifecycle Hooks**: `setUp()` and `tearDown()` methods that run before/after each test.
*   **Mocking Capabilities**: The `$this->createMock()` method for creating test doubles.

### Example: `UnaryExpressionTest.php`
This is a complete example of a first test file. It should be placed at `tests/Unit/Contracts/Expressions/UnaryExpressionTest.php`.

```php
<?php

namespace mnaatjes\ABAC\Tests\Unit\Contracts\Expressions;

use mnaatjes\ABAC\Contracts\Attribute;
use mnaatjes\ABAC\Contracts\Expressions\UnaryExpression;
use mnaatjes\ABAC\Contracts\PolicyContext;
use mnaatjes\ABAC\Support\AttributeAccessor;
use mnaatjes\ABAC\Tests\Stubs\User;
use PHPUnit\Framework\TestCase;

class UnaryExpressionTest extends TestCase
{
    public function test_not_operator_inverts_a_true_value(): void
    {
        // 1. ARRANGE

        // Create a dummy context. It's required by the method signature,
        // but won't be used in this test because we are testing a 'literal'.
        $context = new PolicyContext(
            actor: new User('test-user'),
            subjects: [],
            environment: []
        );
        $accessor = new AttributeAccessor();

        // This attribute's value is self-contained and doesn't need the context.
        $literalTrueAttribute = new Attribute(entity: 'literal', literal: true);

        $expression = new UnaryExpression(
            operator: '!',
            operand: $literalTrueAttribute
        );

        // 2. ACT
        $result = $expression->evaluate($context, $accessor);

        // 3. ASSERT
        $this->assertFalse($result);
    }
}
```

## 5. Advanced Concepts: Mocks & Stubs

To test a "unit" in isolation, you must prevent its dependencies from interfering. We do this using **Mocks** (also known as test doubles or fakes).

A mock is a fake object that simulates the behavior of a real dependency. For example, when testing the `PDP`, we don't want to test the real `PRP` and the file system. We want to test the `PDP`'s logic *in isolation*.

### A Mocking Example
Here is how you would test that the `PDP` correctly denies access, using a mock `PRP`.

```php
public function test_it_returns_deny_when_a_deny_policy_matches(): void
{
    // ARRANGE
    // 1. Create a mock/fake version of the PRP.
    $prpMock = $this->createMock(PRP::class);

    // 2. Create a fake "Deny" Policy object.
    $denyPolicy = new Policy(..., effect: 'deny', ...);

    // 3. Tell the mock PRP to return our fake policy when its findTargetPolicies method is called.
    $prpMock->method('findTargetPolicies')->willReturn([$denyPolicy]);

    // 4. Create the real PDP we want to test, giving it the fake PRP.
    $pdp = new PDP($prpMock);
    $context = new PolicyContext(...);

    // ACT
    $decision = $pdp->decide('some-action', $context);

    // ASSERT
    // Assert that the PDP's final decision was to deny access.
    $this->assertFalse($decision->allowed);
}
```

## 6. Project Test Plan

Here is a roadmap of test classes to create to achieve good test coverage for the library.

*   **`tests/Unit/AttributeTest.php`**: Test the `getValue` method for all entities (`actor`, `subject`, `environment`, `literal`) and its dot-notation logic.
*   **`tests/Unit/Expressions/UnaryExpressionTest.php`**: Test all operators (`!`, `empty`, `is-null`, etc.).
*   **`tests/Unit/Expressions/BinaryExpressionTest.php`**: Test all comparison operators (`==`, `>`, `<=`, etc.).
*   **`tests/Unit/Expressions/FunctionExpressionTest.php`**: Test that it correctly calls the `FunctionRegistry`. Requires mocking the registry.
*   **`tests/Unit/Foundation/PDPTest.php`**: Test the core decision logic (`permit`, `deny`, deny-overrides, default-deny). Requires mocking the `PRP`.
*   **`tests/Unit/Foundation/PRPTest.php`**: Test the policy filtering logic. Requires mocking the `PolicyManager`.
*   **`tests/Integration/JSONPolicyManagerTest.php`**: An integration test that verifies a real policy file can be loaded and parsed correctly.
