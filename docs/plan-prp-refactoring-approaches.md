# PRP Refactoring and Improvement Plan

This document outlines the analysis of the current `PRP::findTargetPolicies` method and proposes three alternative approaches for its refinement. The goal is to improve performance, clarity, and flexibility.

## Analysis of the Current Method

The current implementation feels "clunky" for several reasons:

1.  **Fetches Everything:** It starts by loading *all* policies from the `PolicyManager` via `findAll()`, which is inefficient for large policy sets.
2.  **Multiple Passes:** It filters policies in separate, sequential steps (first for `action`, then for `actor`, then for `subjects`), resulting in multiple loops over the data.
3.  **Complex Logic:** The nested use of `array_reduce` and `array_map` for subject filtering is difficult to read and debug.
4.  **Tight Coupling:** The filtering logic is tightly coupled to concrete class names (e.g., `User`, `Post`) via `getShortName()`, making policies rigid and less reusable.

---

## Approach 1: The "Single-Pass Filter" (Simple Refactor)

This approach improves performance and clarity by consolidating the filtering logic into a single loop without changing the overall architecture.

### Outline:

1.  Fetch all policies from the `PolicyManager` once (`$this->findAll()`).
2.  Get the actor and subject class names.
3.  Create a single `foreach` loop to iterate over the policies.
4.  Inside the loop, use a series of `if` conditions to check if a policy is a match:
    *   The action is in the policy's actions.
    *   **AND** the actor's class name is in the policy's actors.
    *   **AND** at least one of the subject class names is in the policy's subjects.
5.  If all conditions are met, add the policy to a results array.
6.  Return the results array after the loop finishes.

---

## Approach 2: The "Criteria Object" (Architectural Refactor)

This approach improves the separation of concerns by having the `PRP` tell the `PolicyManager` *what it's looking for*, letting the storage layer handle the actual filtering.

### Outline:

1.  **Create a `PolicyCriteria` DTO:** This is a simple object to hold the search criteria.
2.  **Update `PolicyManager` Interface:** Add a new method: `findByCriteria(PolicyCriteria $criteria): array`.
3.  **Refactor the `PRP`:**
    *   Build a `PolicyCriteria` object from the incoming `$action` and `$context`.
    *   Call `$this->pm->findByCriteria($criteria)` and return the result.
4.  **Implement in `FilePolicyManager`:** The `findByCriteria` method would contain the "Single-Pass Filter" logic from Approach 1.
5.  **Future Benefit:** A `DBPolicyManager` could translate this DTO directly into an efficient SQL `WHERE` clause.

### Sample `PolicyCriteria` DTO

```php
<?php

namespace mnaatjes\ABAC\Contracts;

/**
 * PolicyCriteria
 *
 * A Data Transfer Object (DTO) that represents the criteria for finding relevant policies.
 */
final readonly class PolicyCriteria
{
    /**
     * @param string $action The action being requested (e.g., 'edit', 'view').
     * @param string[] $actorCategories The categories the acting entity belongs to.
     * @param string[] $subjectCategories The categories the subject entities belong to.
     * @param string[] $environmentKeys The keys of any attributes present in the environment.
     */
    public function __construct(
        public string $action,
        public array $actorCategories,
        public array $subjectCategories,
        public array $environmentKeys = []
    ) {}
}
```

---

## Approach 3: The "Category System" (Decoupling & Flexibility)

This advanced approach decouples policies from concrete class names, making them more abstract and reusable. It is best used in combination with Approach 2.

### Outline:

1.  **Define a `Categorizable` Interface:**
    ```php
    interface Categorizable {
        public function getPolicyCategories(): array;
    }
    ```
2.  **Update `PIP` Interface:** Have `PIP` extend `Categorizable`.
3.  **Implement in Models:** Your `User`, `Post`, etc., classes would implement `getPolicyCategories()` to return an array of abstract string categories (e.g., `['user', 'actor']`).
4.  **Write Policies Against Categories:** The `actors` and `subjects` arrays in `policies.json` will now contain these abstract categories instead of class names.

### Example JSON Change

**BEFORE (Using Class Names):**
```json
{
  "actors": ["User"],
  "subjects": ["Post"],
  "actions": ["edit"],
  "...": "..."
}
```

**AFTER (Using Category Names):**
```json
{
  "actors": ["user"],
  "subjects": ["content"],
  "actions": ["edit"],
  "...": "..."
}
```