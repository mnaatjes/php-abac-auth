# Ref: The Open/Closed Principle (OCP)

This document provides an in-depth explanation of the Open/Closed Principle, a fundamental concept of object-oriented design and the "O" in the SOLID principles.

### The Principle Defined

First stated by Bertrand Meyer, the principle says:

> Software entities (classes, modules, functions, etc.) should be **open for extension**, but **closed for modification**.

This sounds like a paradox, but it's the key to building systems that are stable, maintainable, and easy to change without breaking what already works.

---

## Core Concepts Explained

### What Does "Closed for Modification" Mean?

This means that once a class has been developed, tested, and is working correctly, you should not have to open its source code and change it to add a new, related feature.

*   **Why?** Every time you modify existing code, you risk introducing bugs into a part of the system that was previously stable. The goal is to minimize this risk.
*   **Analogy:** Think of a sealed engine block in a car. It has been tested and is known to work reliably. If you want more power, you don't crack open the engine block and start welding new parts inside. You add external components.

### What Does "Open for Extension" Mean?

This means that you should be able to add new functionality to your system by **adding new code**, not by changing old code.

*   **Why?** This is the safe way to evolve a system. New code can be tested in isolation, and it doesn't risk destabilizing the core, existing logic.
*   **Analogy:** The car engine is "open" to being extended with a turbocharger. The turbo is a new component that follows a standard interface (the intake and exhaust ports) to work with the engine. You added a major new capability without ever modifying the engine block itself.

### How is This Possible? Abstraction!

The mechanism that makes OCP possible is **abstraction**. By depending on interfaces or abstract classes rather than concrete implementations, you can create "pluggable" points in your architecture.

---

## Example: A Report Generator

Let's consider a class that generates reports in different formats.

### The "Bad" Way (Violates OCP)

Initially, you only need PDF and CSV reports. It's tempting to write a class with a `switch` statement.

```php
class ReportGenerator
{
    public function generate(string $reportType, array $data): string
    {
        switch ($reportType) {
            case 'pdf':
                // ... logic to generate a PDF string ...
                return 'PDF Report Content';
            case 'csv':
                // ... logic to generate a CSV string ...
                return 'CSV,Report,Content';
            default:
                throw new \Exception("Unsupported report type.");
        }
    }
}
```

*   **The Problem:** Now, your boss asks for XML reports. To add this feature, you are forced to open the `ReportGenerator` class and **modify it** by adding a new `case 'xml':`. This is a direct violation of the "closed for modification" rule.

### The "Good" Way (Adheres to OCP)

The correct approach is to create a "pluggable" point using an interface.

**Step 1: Define the Abstraction (The Interface)**

```php
interface ReportFormattable
{
    public function format(array $data): string;
}
```

**Step 2: Create Concrete Implementations (The "Plugs")**

```php
class PdfFormatter implements ReportFormattable
{
    public function format(array $data): string
    {
        return 'PDF Report Content';
    }
}

class CsvFormatter implements ReportFormattable
{
    public function format(array $data): string
    {
        return 'CSV,Report,Content';
    }
}
```

**Step 3: Refactor the Main Class to Use the Abstraction**

The `ReportGenerator` no longer knows about specific formats. It only knows how to use an object that fulfills the `ReportFormattable` contract.

```php
class ReportGenerator
{
    public function generate(ReportFormattable $formatter, array $data): string
    {
        // The generator delegates the formatting work.
        // It is now CLOSED for modification.
        return $formatter->format($data);
    }
}
```

*   **The Solution:** Now, when your boss asks for XML reports, you **do not touch any of the existing files**. You simply **add a new file**:

    ```php
    // XmlFormatter.php (NEW FILE)
    class XmlFormatter implements ReportFormattable
    {
        public function format(array $data): string
        {
            return '<report><content/></report>';
        }
    }
    ```

Your system is **open for extension** because you added a new class, and it was **closed for modification** because you didn't have to change any of the original, working code.

---

## Decision Flowchart & Matrix

How do you decide when to apply this principle?

### Flowchart

```mermaid
graph TD
    A[Need to add a new behavior?] --> B{Does this require changing an existing, stable class?};
    B -- Yes --> C{Is the change because of a new type or variation? (e.g., new report format, new storage driver)};
    C -- Yes --> D[OCP VIOLATION!];
    D --> E[Refactor: Create an interface for the different variations.];
    E --> F[Create separate classes implementing the interface for each variation.];
    F --> G[Modify the original class to depend on the interface, not the concrete types.];
    G --> H[Success! The class is now closed for modification but open for extension.];
    C -- No --> I[Is the change a bug fix or a core logic improvement?];
    I -- Yes --> J[Okay to modify. OCP doesn't forbid bug fixing or refactoring.];
    B -- No. I can add a new class without changing old ones. --> K[Great! You are following OCP.];
```

### Decision Matrix

| Your Situation | Key Question | Action | Principle | 
| :--- | :--- | :--- | :--- |
| "I need to add a YAML policy file type." | Do I have to add an `elseif` to my factory? | Yes? This is a pragmatic violation. The pure solution is to use a registration pattern. | **OCP Violation** | 
| "I need to fix a bug in how JSON policies are parsed." | Am I adding a new behavior or fixing a broken one? | Fix the bug directly inside the `JsonPolicyManager` class. | **Bug Fixing** | 
| "I need to add logging to all policy-saving methods." | Can I do this without changing every manager? | Use the **Decorator Pattern**. Create a `LoggingPolicyManager` that wraps another `PolicyManager`. | **Adhering to OCP** | 
| "I need to add a `findByOwnerId` method." | Does this belong on the interface for all managers? | Yes? Add the method to the `PolicyManager` interface, then implement it in all concrete classes. | **Evolving the Contract** |

---

### Connection to the ABAC Project

Our discussion about the `ABACFactory` is a perfect example of this principle in action.

1.  **The `match` statement:** We identified that having a `match` statement in the factory to decide which `PolicyManager` to create is a pragmatic, but strict, **violation of OCP**. To add a new storage type, the factory itself must be modified.

2.  **The `PolicyManager` Interface:** The reason we created the `PolicyManager` interface was to ensure the `PAP` and `PRP` classes **adhere to OCP**. The `PAP` and `PRP` are closed for modification; their logic never needs to change. They are open for extension because you can give them any new type of `PolicyManager` (e.g., `YamlPolicyManager`, `DatabasePolicyManager`) and they will work perfectly without any changes to their own code.

This illustrates that OCP is a guiding principle. We contain the small, pragmatic violation to a single factory at the edge of the system, which allows the core internal components of the system to follow the principle perfectly.
