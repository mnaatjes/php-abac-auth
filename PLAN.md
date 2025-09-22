# Reference: Authorization Concepts (PDP, PIP, PEP)

This document summarizes the architectural concepts of the Policy Decision Point (PDP), Policy Information Point (PIP), and Policy Enforcement Point (PEP).

## 1. What are PDP, PIP, and PEP?

These terms, formalized by the XACML standard, describe distinct roles within an authorization system. Understanding their separation is key to building robust applications.

*   **PDP (Policy Decision Point):** The brain of the operation. This is the ultimate source of truth that defines the policy rules. It is the component that makes the final decision.
    *   *Analogy:* The club owner who writes the guest list and the rules (e.g., "no sneakers, members only").
    *   *In Code:* The `PostPolicy` class, the logic inside a `Gate::define()` closure, or your `categories.php` file.

*   **PIP (Policy Information Point):** The source of information needed to make a decision. These are your data objects and application state.
    *   *Analogy:* The guest's ID, their membership card, and their attire.
    *   *In Code:* The currently authenticated `User` object, the `Post` model being evaluated, the current time, the user's IP address.

*   **PEP (Policy Enforcement Point):** The bouncer at the door. This is the component that actually enforces the policy. It guards a resource or action, asks the PDP for a decision, and then acts on that decision.
    *   *Analogy:* The bouncer who checks the ID against the guest list and either opens the rope or denies entry.
    *   *In Code:* An `AuthMiddleware`, the `Gate::allows()` method, or a `can()` check in a controller.

## 2. Standard vs. Pattern: XACML in PHP

PHP frameworks do **not** implement the formal, heavyweight XACML standard directly. The XML-based nature of the standard is often seen as too cumbersome for most web development.

However, PHP frameworks **absolutely follow the XACML architectural pattern**. The concepts of separating policy, information, and enforcement are fundamental to modern best practices.

For example, Laravel's `Gate` maps perfectly:
- **PDP:** The `AuthServiceProvider` where `Gate::define()` rules are written.
- **PIP:** The `User` and `Post` models passed to the gate.
- **PEP:** The `Gate::allows()` method or the `can:` middleware.

## 3. How They Interact: The "Hub and Spoke" Model

The components are **not** implemented in a simple chain. Doing so would create a tightly-coupled system. Instead, they interact in a **hub-and-spoke** model where the **PEP is the central hub**.

This interaction model is a critical best practice because it decouples the components.

1.  An action is attempted in the application (e.g., a controller method).
2.  The application code calls the **PEP** to check for permission.
3.  The **PEP** gathers the necessary data from the **PIPs** (e.g., the current user, the target resource).
4.  The **PEP** consults the **PDP**, passing it the information from the PIPs to get a decision.
5.  The **PDP** evaluates its rules and returns a simple "Permit" or "Deny" decision to the PEP.
6.  The **PEP** enforces the decision, either by letting execution continue or by stopping it (e.g., throwing a 403 exception).

### Interaction Flowchart

This sequence diagram shows the PEP acting as the central orchestrator.

```mermaid
sequenceDiagram
    participant AppCode as Application Code (e.g., Controller)
    participant PEP as Policy Enforcement Point (e.g., Gate, Middleware)
    participant PIPs as Policy Information Points (e.g., User/Post Models)
    participant PDP as Policy Decision Point (e.g., Policy Class)

    AppCode->>PEP: Can I 'delete' this 'Post' object?
    Note over PEP: PEP is now in control.

    PEP->>PIPs: Give me the current User and the Post details.
    PIPs-->>PEP: Here is the User and Post data.

    PEP->>PDP: Evaluate 'delete' policy with this User and Post.
    PDP-->>PEP: Decision: Permit (true).

    PEP-->>AppCode: Yes, you are allowed.
    AppCode->>AppCode: Proceed with post deletion.
```

## 4. Why This Structure is a Best Practice

Separating these roles is fundamental to good software design:

*   **Single Responsibility Principle (SRP):** Each component does one job, making the system easier to understand and maintain.
*   **Flexibility:** You can change the policy rules (PDP) without ever touching the enforcement logic (PEP).
*   **Testability:** Each component can be unit-tested in isolation.

## 5. OOP Implementation: Dependency Injection

The ideal OOP relationship for these components is **Dependency Injection**. The class containing the PEP (e.g., `ServiceRegistry`) should not create its dependencies. Instead, the PDP facade (e.g., `CategoryManager`) is passed into, or "injected" into, the PEP's class, usually via the constructor.

This is a best practice for several reasons:

1.  **Decoupling:** The `ServiceRegistry` doesn't know *how* the `CategoryManager` gets its rules. It only knows that it has an object that can answer the question "Is this allowed?".
2.  **Testability:** When you unit-test the `ServiceRegistry`, you can easily inject a "mock" `CategoryManager` that returns `true` or `false` on command. This allows you to test the PEP's logic in complete isolation.
3.  **Flexibility:** If you ever change how you store your policies (e.g., moving from a file to a database), you only need to write a new Manager class. The `ServiceRegistry` code doesn't change.

### Simple Schematic of the Interaction

A class diagram is the perfect way to visualize this OOP relationship.

```mermaid
classDiagram
    direction LR

    class ServiceRegistry {
        <<PEP Location>>
        -CategoryManager categoryManager
        +register(alias, metadata)
    }

    class CategoryManager {
        <<PDP Facade>>
        -array rules
        +isKeyAllowedForCategory(cat, key) bool
    }

    class categories_php {
        <<Data Dictionary (PDP)>>
        PHP Array
    }
    
    class Metadata {
        <<Policy Information (PIP)>>
        Array passed to register()
    }

    ServiceRegistry o-- CategoryManager : injects
    CategoryManager ..> categories_php : loads
    ServiceRegistry ..> Metadata : receives

```

**What this schematic shows:**

1.  **`ServiceRegistry` has a `CategoryManager`:** The solid line with the circle (`o--`) shows that `ServiceRegistry` has a reference to a `CategoryManager` instance. This is the dependency injection.
2.  **`CategoryManager` loads `categories.php`:** The dotted line (`..>`) shows that the `CategoryManager` depends on and loads its rules from the `categories.php` file (the PDP).
3.  **`ServiceRegistry` receives `Metadata`:** The `ServiceRegistry`'s `register` method receives the metadata array (the PIP) as an argument, which it will then ask the `CategoryManager` to validate.

## 6. Abstract PHP Implementation Contracts

While specific implementations vary, we can define a set of interfaces to represent the abstract roles of PDP, PIP, and PEP. This helps in creating a clean, type-safe, and decoupled authorization system.

### a) The Policy Information Point (PIP)

The PIP is the data being judged. In OOP, these are typically your existing domain models. We can use a marker interface to signify that an object can act as a PIP.

**PHP Interface:**
```php
/**
 * A marker interface indicating that an object can provide
 * information for a policy decision.
 */
interface PolicyInformationPoint {}
```

**Example Implementation:**
```php
class User implements PolicyInformationPoint 
{
    public function __construct(public int $id, public array $roles) {}
}

class Post implements PolicyInformationPoint
{
    public function __construct(public int $authorId) {}
}
```

**Class Diagram:**
```mermaid
classDiagram
    class PolicyInformationPoint {
        <<interface>>
    }
    class User {
        +int id
        +array roles
    }
    class Post {
        +int authorId
    }
    PolicyInformationPoint <|.. User
    PolicyInformationPoint <|.. Post
```

### b) The Policy Decision Point (PDP)

The PDP contains the actual policy logic. Its interface needs a method to make a decision based on the information (PIPs) provided.

**PHP Interface:**
```php
interface PolicyDecisionPoint
{
    /**
     * Decides if an action is permitted based on the given context.
     */
    public function decide(string $action, PolicyInformationPoint $actor, ?PolicyInformationPoint $subject = null): bool;
}
```

**Example Implementation:**
```php
class BlogPostPolicy implements PolicyDecisionPoint
{
    public function decide(string $action, PolicyInformationPoint $actor, ?PolicyInformationPoint $subject = null): bool
    {
        // Ensure the actor is a User and subject is a Post for context
        if (!$actor instanceof User || !$subject instanceof Post) {
            return false;
        }

        return match ($action) {
            'edit-post' => $actor->id === $subject->authorId || in_array('admin', $actor->roles),
            'delete-post' => in_array('admin', $actor->roles),
            default => false,
        };
    }
}
```

### c) The Policy Enforcement Point (PEP)

The PEP orchestrates the check. It's a service that uses a PDP to make a decision and then acts on it. It is implemented as a concrete class, not an interface, but it depends on the PDP *interface*.

**Example Implementation:**
```php
class AuthorizationGate
{
    public function __construct(private PolicyDecisionPoint $pdp) {}

    /**
     * Checks if an action is allowed. Throws an exception if not.
     * This is the Policy Enforcement Point.
     */
    public function authorize(string $action, PolicyInformationPoint $actor, ?PolicyInformationPoint $subject = null): void
    {
        if ($this->pdp->decide($action, $actor, $subject) === false) {
            throw new AuthorizationException("Action '{$action}' is not allowed.");
        }
    }
}
```

**Class Diagram (PEP & PDP Relationship):**
```mermaid
classDiagram
    class AuthorizationGate {
        <<PEP>>
        -PolicyDecisionPoint pdp
        +authorize(action, actor, subject)
    }
    class PolicyDecisionPoint {
        <<interface>>
        +decide(action, actor, subject) bool
    }
    class BlogPostPolicy {
        <<PDP>>
        +decide(action, actor, subject) bool
    }

    AuthorizationGate o-- PolicyDecisionPoint : injects
    PolicyDecisionPoint <|.. BlogPostPolicy
```

## 7. Generic Example: Blog Post Authorization

Here is how all the pieces work together in a generic controller.

```php
// 1. Your services are constructed with dependency injection.
$blogPolicy = new BlogPostPolicy(); // This is our PDP.
$gate = new AuthorizationGate($blogPolicy); // Our PEP, injected with the PDP.

// 2. Your application receives a request.
$currentUser = new User(id: 123, roles: ['editor']); // PIP
$postToEdit = new Post(authorId: 123); // PIP

// 3. The controller uses the PEP to guard an action.
class PostController
{
    public function __construct(private AuthorizationGate $gate) {}

    public function update(User $currentUser, Post $postToEdit, array $newData)
    {
        try {
            // The PEP is called here.
            $this->gate->authorize('edit-post', $currentUser, $postToEdit);

            // This code only runs if authorize() does not throw an exception.
            echo "Authorization successful! Updating post...";
            // ... update post logic ...

        } catch (AuthorizationException $e) {
            echo "Caught exception: " . $e->getMessage();
            // Return a 403 Forbidden response.
        }
    }
}

// 4. Running the example.
$controller = new PostController($gate);
$controller->update($currentUser, $postToEdit, ['title' => 'New Title']);
// Output: Authorization successful! Updating post...
```

## 8. Formal Concepts & Naming

The hub-and-spoke architecture we have discussed is an implementation of several formal industry concepts.

### Attribute-Based Access Control (ABAC)

This is the most accurate, industry-standard name for this authorization *strategy*. In an ABAC system, authorization decisions are made by evaluating **attributes** (properties) against a set of policies. This is more flexible than simple Role-Based Access Control (RBAC).

*   **Attributes** are provided by the **PIPs** (e.g., a user's role, a post's status, the current time).
*   **Policies** are defined in the **PDP** (e.g., "Allow users with the 'editor' role to 'edit' a post if the post's status is 'draft'.").
*   The **PEP** gathers the attributes and queries the PDP.

The PDP/PIP/PEP model is the classic architecture for implementing an ABAC system.

### The Mediator Pattern

From a software design pattern perspective, this architecture is a clear example of the **Mediator Pattern**. 

The **PEP** acts as the **Mediator**. It manages and coordinates communication between the other components (the Application Code, the PDP, the PIPs). The application code only talks to the Mediator (the PEP), and is completely decoupled from the complex policy logic in the PDP. This reduces direct dependencies and makes the system easier to manage.

## 9. Creating a Reusable ABAC Library

It is an excellent idea and a standard practice to isolate this core logic into a reusable Composer package. This is a form of **Component-Based Development**.

### Package Structure

A minimal, reusable package would have the following structure:

```
/php-abac-auth/
├── composer.json
└── src/
    ├── Contracts/
    │   ├── PolicyDecisionPoint.php
    │   └── PolicyInformationPoint.php
    ├── Gate.php
    └── Exception/
        └── AuthorizationException.php
```

### File Contents

**`composer.json`**
```json
{
    "name": "your-vendor/abac-auth",
    "description": "A simple, reusable library for Attribute-Based Access Control.",
    "type": "library",
    "license": "MIT",
    "autoload": {
        "psr-4": {
            "YourVendor\\AbacAuth\\": "src/"
        }
    },
    "require": {
        "php": ">=8.1"
    }
}
```

**`src/Contracts/PolicyInformationPoint.php`**
```php
<?php
namespace YourVendor\AbacAuth\Contracts;

interface PolicyInformationPoint {}
```

**`src/Contracts/PolicyDecisionPoint.php`**
```php
<?php
namespace YourVendor\AbacAuth\Contracts;

interface PolicyDecisionPoint
{
    public function decide(string $action, PolicyInformationPoint $actor, ?PolicyInformationPoint $subject = null): bool;
}
```

**`src/Gate.php` (The PEP)**
```php
<?php
namespace YourVendor\AbacAuth;

use YourVendor\AbacAuth\Contracts\PolicyDecisionPoint;
use YourVendor\AbacAuth\Contracts\PolicyInformationPoint;
use YourVendor\AbacAuth\Exception\AuthorizationException;

class Gate
{
    public function __construct(private PolicyDecisionPoint $pdp) {}

    public function authorize(string $action, PolicyInformationPoint $actor, ?PolicyInformationPoint $subject = null): void
    {
        if ($this->pdp->decide($action, $actor, $subject) === false) {
            throw new AuthorizationException("Action '{$action}' is not allowed.");
        }
    }
    // ... other helper methods like allows() / denies() ...
}
```

### Library vs. Application Code

This diagram shows the clear separation between the generic **Library** and the specific **Application Code** that uses it.

```mermaid
graph TD
    subgraph "Your Application Code"
        direction LR
        AppPDP["AppPolicy (implements PDP)"]
        AppPIP["User/Post (implements PIP)"]
        AppCode["Controller / Service"]
    end

    subgraph "Your Reusable Library"
        direction LR
        LibGate["Gate (PEP)"]
        LibPDP["PolicyDecisionPoint (Interface)"]
        LibPIP["PolicyInformationPoint (Interface)"]
    end

    AppCode -- uses --> LibGate
    LibGate -- injects --> LibPDP
    AppPDP -- dependency --> LibPDP
    AppPIP -- dependency --> LibPIP
```

### How to Use the Package

A developer using your library would:

1.  **Implement your library's interfaces** in their own application-specific classes (`User`, `Post`, `AppPolicy`).
2.  **Instantiate their custom PDP** (`$myAppPolicy = new AppPolicy();`).
3.  **Instantiate your library's `Gate` (PEP)**, injecting their custom PDP into it (`$gate = new Gate($myAppPolicy);`).
4.  **Use the `$gate`** in their application to authorize actions.
