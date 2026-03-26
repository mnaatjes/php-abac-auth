# ABAC-Auth Library Design Summary

This document summarizes the architectural decisions and design patterns established for the PHP ABAC-Auth library.

---

### Core Architectural Rules (The 7 Rules)

1.  **Core Entities are PIPs:** The `actor` and `subjects` in a `PolicyContext` must be objects that implement the `PolicyInformationPoint` interface.
2.  **Actions are Strings:** The `action` is always a simple, human-readable string (e.g., `'edit-post'`).
3.  **Environment is Flexible:** The `environment` array can contain simple scalar types OR `PolicyInformationPoint` objects.
4.  **Loose Contract for PIPs:** The library uses a "loose contract." PIPs are not required to implement a specific method like `getAttribute()`. Instead, a smart `AttributeAccessor` within the PDP uses reflection to find and call existing getters (e.g., `getRole()`) or access public properties.
5.  **Schema Validation:** The PDP must perform a "fail-fast" schema validation step. Before evaluating a policy, it verifies that the supplied PIPs can provide all attributes required by that policy's rules.
6.  **Deny-Overrides Principle:** When combining results from multiple policies, a `deny` decision always wins.
7.  **Default-Deny Principle:** If no policy explicitly grants permission for a request, the final decision is `deny`.

### Core Components & Classes

*   **`PolicyContext`**: A Data Transfer Object (DTO) that holds the `actor` (a PIP), `subjects` (an array of PIPs), and `environment` (a mixed array).

*   **`AttributeAccessor`**: A dedicated helper class used by the PDP. Its single responsibility is to retrieve the value of a named attribute from a PIP object, implementing the "loose contract" logic.

*   **Object-Oriented Policy Structure**: Policies loaded from JSON are deserialized into a graph of strongly-typed objects:
    *   **`Policy`**: The root object, containing policy metadata and a list of expressions.
    *   **`ExpressionInterface`**: An interface with a single `evaluate()` method. The `Policy` object holds an array of these.
    *   **`UnaryExpression`**: Implements the interface. Represents a rule with two properties: an `operator` (string) and a single `operand` (`Attribute` object).
    *   **`BinaryExpression`**: Implements the interface. Represents a rule with three properties: a `leftHandSide` (`Attribute` object), an `operator` (string), and a `rightHandSide` (`Attribute` object).
    *   **`FunctionExpression`**: Implements the interface. Represents a custom function call with two properties: a `functionName` (string) and an `arguments` (an array of `Attribute` objects).
    *   **`Attribute`**: Represents a single operand. It has a `source` (`actor`, `subject`, `environment`, or `literal`), a `name` (for context attributes), and a `literalValue` (for hard-coded values in the policy).

### Integration Patterns

*   **PIP Identification**: A developer identifies their own models (`User`, `Post`) as PIPs by adding `implements PolicyInformationPoint`. No adapters are needed for these.
*   **Handling `$_SESSION` and Arrays**: Simple data from arrays or superglobals is not wrapped in a PIP. It is placed directly into the `environment` array of the `PolicyContext`.
*   **Adapter Pattern for Third-Party Code**: To use an object from an external, unmodifiable library as a PIP, the developer creates a simple **Adapter Class** that wraps the third-party object and implements the `PolicyInformationPoint` interface.

### Policy Administration Point (PAP)

The PAP is the concept for managing policies. We identified three possible implementations:
1.  **Config File**: A developer-centric approach for declaring core policies in a version-controlled PHP file.
2.  **CLI Tool**: An interactive tool for developers to safely generate and validate policies. This is the ideal place for a "smart assistant" that inspects PIP classes for required attributes.
3.  **Web GUI**: An interface for non-technical administrators to manage policies in a live environment.
