# Reference: The Schema Validator Pattern

This document outlines a simpler, more direct architectural pattern for data validation, designed for scenarios where a full ABAC (Attribute-Based Access Control) system is overly complex.

This design was developed from the following context:
* The "actor" or user performing the action is irrelevant.
* The "action" is always a single, repetitive check (e.g., "is this value in a list?").
* The goal is to create a reusable library or package for this specific, structured enforcement.

---

## 1. Redefining the Vocabulary

When the context is simplified, the relational terms of ABAC (Actor, Subject, Action) can be replaced with a more direct vocabulary:

* **Schema:** This is the **source of truth**—the definition of what is valid. It is the rule set.
    * *Example:* An array of approved category names loaded from a `config.php` file.

* **Candidate:** This is the **value being tested** for validity.
    * *Example:* The new category name a user is attempting to add.

* **Validator:** This is the **object that performs the check**. It compares a `Candidate` against the `Schema`.

This leads to a clear and simple description of the process: a **Validator** checks if a **Candidate** conforms to a **Schema**.

---

## 2. Formal Concepts and Influences

While "Schema Validator Pattern" is a descriptive phrase for this architecture, it's grounded in formal, industry-standard concepts.

### a) The Standard: JSON Schema

JSON Schema is a language-agnostic, formal **specification** for validating the structure and content of JSON data. The concepts it uses are directly applicable here:
* It provides a vocabulary for defining a schema (e.g., `type`, `properties`, `required`).
* It includes an `enum` keyword, which is designed for the exact use case of defining a list of allowed values.

Our `config.php` file acts as a PHP-native schema definition, and our goal of checking a category against a list is identical to validating against a JSON Schema `enum`.

### b) The Design Pattern: The Specification Pattern

The most relevant formal design pattern for this task is the **Specification Pattern**. This pattern is designed to encapsulate individual business rules into composable objects.

* **Core Idea:** Each "specification" object represents a single rule and has a method like `isSatisfiedBy(candidate)`.
* **Application:** We can create a specification that represents our "allowed list" rule. The `Validator` class then uses this specification to perform its check.

**Example of the Specification Pattern:**
```php
interface Specification
{
    public function isSatisfiedBy(mixed $candidate): bool;
}

// A concrete specification for our use case
class AllowedValuesSpecification implements Specification
{
    public function __construct(
        private readonly array $allowedValues
    ) {}

    public function isSatisfiedBy(mixed $candidate): bool
    {
        return in_array($candidate, $this->allowedValues);
    }
}

// ---
// --- Usage ---
// ---
$allowed = ['string', 'integer', 'banana'];
$spec = new AllowedValuesSpecification($allowed);

$spec->isSatisfiedBy('banana'); // true
$spec->isSatisfiedBy('apple');  // false
```

---

## 3. The Distilled Course of Actions

Every schema validation process can be distilled into four fundamental steps. This is the core algorithm the library will implement.

1.  **Acquire Schema:** The validator obtains the set of rules.
    *   *(Example: Loading the `config.php` file and extracting the array of allowed keys).*

2.  **Receive Candidate:** The validator is given the piece of data it needs to check.
    *   *(Example: The string `'banana'` is passed to the `validate()` method).*

3.  **Evaluate:** The validator compares the candidate data against the rules defined in the schema.
    *   *(Example: `in_array('banana', ['string', 'integer', 'banana'])` is executed).*

4.  **Report Result:** The validator communicates the outcome. This can be done by:
    *   Returning a simple `boolean` (`true` or `false`).
    *   Returning a detailed `Result` object.
    *   Throwing an `Exception` on failure (the "fail-fast" approach).

---

## 4. Proposed Library Design

Based on the Schema Validator architecture, here is a design for a simple, reusable validation library.

### a) The `Schema` Interface

This defines a contract for any source of rules, making the library extensible.

```php
<?php
namespace YourVendor\SchemaValidator;

interface Schema
{
    /**
     * Returns the array of allowed values.
     * @return list<string>
     */
    public function getAllowedValues(): array;
}
```

### b) A Concrete `PhpFileSchema` Implementation

This implementation loads the schema from a PHP configuration file.

```php
<?php
namespace YourVendor\SchemaValidator;

class PhpFileSchema implements Schema
{
    private array $values;

    public function __construct(string $filePath)
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("Schema file not found: {$filePath}");
        }
        // Assumes the allowed values are the keys of the returned array
        $this->values = array_keys(require $filePath);
    }

    public function getAllowedValues(): array
    {
        return $this->values;
    }
}
```

### c) The `Validator` Class

This is the main workhorse. It is configured with a `Schema` and provides the validation methods.

```php
<?php
namespace YourVendor\SchemaValidator;

class Validator
{
    public function __construct(
        private readonly Schema $schema
    ) {}

    /**
     * Checks if a candidate value is valid against the schema.
     * Throws a ValidationException if invalid.
     */
    public function validate(string $candidate): void
    {
        if (!$this->isValid($candidate)) {
            throw new ValidationException("'{$candidate}' is not a valid value.");
        }
    }

    /**
     * Checks if a candidate value is valid. Returns true or false.
     */
    public function isValid(string $candidate): bool
    {
        return in_array($candidate, $this->schema->getAllowedValues());
    }
}

// A simple custom exception for semantic clarity
class ValidationException extends \Exception {}
```

### d) Example Usage

This is how the final library would be used in practice. The result is clean, testable, and easy to understand.

```php
// 1. Define the schema from your config file.
$schema = new YourVendor\SchemaValidator\PhpFileSchema(__DIR__ . '/Data/config.php');

// 2. Create the validator with that schema.
$validator = new YourVendor\SchemaValidator\Validator($schema);

// 3. Use it for enforcement.
try {
    echo "Checking 'banana': ";
    $validator->validate('banana'); // This will pass silently.
    echo "VALID\n";

    echo "Checking 'apple': ";
    $validator->validate('apple');  // This will throw a ValidationException.
    echo "VALID\n";

} catch (YourVendor\SchemaValidator\ValidationException $e) {
    echo "INVALID - " . $e->getMessage() . "\n";
}
```

---

## 5. The Meaning of "Schema Validation"

In common computer science parlance, "Schema Validation" refers to the **enforcement of business logic**—specifically, the process of ensuring that a piece of *candidate data* (like user input) conforms to the rules defined in a schema.

It does **not** typically refer to validating the structure of the schema file itself. That is a separate process often called "linting the schema" or "validating the schema definition."

An analogy helps clarify this:

*   **The Schema:** A building blueprint. It is the rulebook.
*   **Candidate Data:** The actual, physical building being constructed.
*   **Schema Validation (Enforcement):** Using the blueprint to inspect the **building** to ensure all walls are in the right place and materials are correct. This is the common meaning of the term.
*   **Linting the Schema (Structural Check):** Checking the **blueprint** itself to ensure it's drawn correctly and uses valid symbols. This is a check on the rules, not an enforcement of them.

Therefore, when we use the term "Schema Validation," we are referring to the runtime process of enforcing rules on application data.

---

## 6. Validator vs. Linter: A Separation of Concerns

While a Validator enforces rules at runtime, a **Linter** checks the schema file itself for structural correctness at build-time or developer-time. These two concepts should be separate.

### a) What is "Linting the Schema"?

Linting the schema means performing static analysis on your `config.php` file to ensure it is well-formed *before* it is used by the validator. A linter would ask questions like:

*   Does the schema file exist?
*   Does it return a PHP `array`?
*   Are all the keys in the array strings?

### b) Why Should They Be Separate?

The Validator and the Linter have different jobs and operate at different times in the software lifecycle.

*   **The Validator is a RUNTIME tool.** It runs live in your application. It must be fast, lightweight, and have minimal dependencies.
*   **The Linter is a BUILD-TIME tool.** It runs when a developer is writing code or as part of a CI/CD pipeline before deployment. Its job is to catch errors before the code is ever released.

Mixing these responsibilities would violate the **Single Responsibility Principle**.

### c) Recommendation: A Separate Package

The best practice is to create two focused packages:

1.  `your-vendor/schema-validator`: The core runtime library. Your application would `require` this.
2.  `your-vendor/schema-linter`: A development-only tool. Your project would `require-dev` this and use it in test and build scripts.

This separation allows consumers to use only what they need, keeping the core runtime validator as lean as possible.

