# ABAC System: Architectural Rules

This document outlines the complete set of architectural rules and contracts governing the interaction between the Policy Decision Point (PDP), Policy Information Points (PIPs), and the `PolicyContext`.

---

### The 7 Foundational Rules

1.  **Core Entities are PIPs:** The `actor` and `subjects` passed into the `PolicyContext` **must** be objects that implement the `PolicyInformationPoint` interface. This ensures type-safety and a consistent contract for the primary entities in any policy decision.

2.  **Actions are Strings:** The `action` being evaluated **must** be a simple, human-readable string (e.g., `'edit-post'`, `'view-document'`). This keeps the definition of an action simple, readable, and decoupled from any specific class.

3.  **Environment is Flexible:** The `environment` array within the `PolicyContext` is a flexible, associative key-value store. It **can** contain simple scalar types (integers, strings, booleans) for simple contextual data, or it **can** contain objects that implement `PolicyInformationPoint` for more complex contextual data (e.g., a geolocation object).

4.  **Loose Contract for PIPs:** PIP implementations do not need to implement a rigid, explicit interface method like `getAttribute()`. The system favors a "loose contract" where the PDP uses a smart accessor (e.g., via reflection) to find and use existing getters (e.g., `getRole()`) or public properties on the PIP objects. This prioritizes ease of integration with existing models.

5.  **Schema Validation:** Before evaluating a policy, the PDP **must** first validate that the supplied PIPs in the `PolicyContext` can provide all the attributes required by that policy's rules. If any attribute is missing or inaccessible, the PDP should fail fast with a specific, descriptive error, rather than failing obscurely during evaluation.

6.  **The "Deny-Overrides" Principle:** When multiple policies match a request, their results must be combined safely. If any single policy returns a `deny` decision, the final, combined decision is **`deny`**, regardless of any other policies that may have returned `permit`.

7.  **The "Default-Deny" Principle:** If no policies are found that apply to a given request, or if no policy explicitly returns a `permit` decision, the final outcome **must** be `deny`. Access is only granted when explicitly allowed.
