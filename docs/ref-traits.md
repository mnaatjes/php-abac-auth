# Reference: PHP Traits

This document explains what PHP Traits are, how they work, and why they are a useful tool for code reuse.

## 1. What are Traits?

A Trait is a mechanism for code reuse in single-inheritance languages like PHP. Since a PHP class can only inherit from one parent class, Traits provide a way to "mix in" and share methods among independent classes in different class hierarchies.

Think of a Trait as a "copy-and-paste" for code that is managed by the PHP engine. When you `use` a Trait in a class, its methods and properties are effectively copied into that class, becoming part of its definition.

They are ideal for sharing a common set of functionalities, like logging, authorization helpers, or serialization, without forcing classes into a rigid inheritance structure.

## 2. Basic Implementation

Implementing and using a trait involves two keywords: `trait` and `use`.

1.  **`trait`**: Defines the reusable block of code.
2.  **`use`**: Pulls the trait into a class, making its methods available.

### Example: A Simple Logger

Here is a trait that provides simple logging capabilities to any class that uses it.

**Trait Definition:**
```php
trait Logger
{
    public function log(string $message): void
    {
        // A real implementation would write to a file or service.
        echo date('Y-m-d H:i:s') . ": " . $message . PHP_EOL;
    }
}
```

**Class Usage:**
```php
class UserService
{
    use Logger; // Mix in the Logger trait

    public function createUser(string $name): void
    {
        // We can now use the log() method as if it were defined on this class.
        $this->log("Creating user: {$name}");
        // ... user creation logic ...
    }
}

class ProductService
{
    use Logger; // Reuse the same trait here

    public function createProduct(string $name): void
    {
        $this->log("Creating product: {$name}");
        // ... product creation logic ...
    }
}

$userService = new UserService();
$userService->createUser('Alice'); // Outputs: [Timestamp]: Creating user: Alice

$productService = new ProductService();
$productService->createProduct('Laptop'); // Outputs: [Timestamp]: Creating product: Laptop
```

## 3. Precedence and Conflict Resolution

Things can get complicated when a class uses multiple traits or inherits from a class, and method names overlap.

### a) Precedence Order

The precedence of methods is as follows:
1.  **Current Class:** A method defined directly in the class will always override a method from a trait.
2.  **Trait:** A method from a trait will override an inherited method from a parent class.
3.  **Parent Class:** The inherited method is used only if no method exists in the current class or in any used traits.

### b) Conflict Resolution with Multiple Traits

If you `use` two traits that both define a method with the same name, PHP will produce a fatal error unless you explicitly resolve the conflict using the `insteadof` and `as` keywords.

**Example:**
```php
trait Sharable
{
    public function share(): string
    {
        return 'Sharing content.';
    }
}

trait Likeable
{
    public function share(): string // Same method name as in Sharable
    {
        return 'Sharing a link.';
    }
}

class Post
{
    // This would cause a fatal error:
    // use Sharable, Likeable;

    // To fix it, we must resolve the conflict:
    use Sharable, Likeable {
        Sharable::share insteadof Likeable; // Explicitly choose Sharable's version of share()
        Likeable::share as shareLink;       // Make Likeable's version available under a new name (alias)
    }
}

$post = new Post();
echo $post->share();     // Outputs: Sharing content.
echo $post->shareLink(); // Outputs: Sharing a link.
```

## 4. Advanced Features

### a) Changing Method Visibility

You can change the visibility (e.g., from `public` to `protected` or `private`) of a trait method within the context of the using class.

```php
trait MyTrait
{
    public function publicMethod() { /* ... */ }
    protected function protectedMethod() { /* ... */ }
}

class MyClass
{
    use MyTrait {
        // Make publicMethod protected in this class
        publicMethod as protected;

        // Make protectedMethod public in this class
        protectedMethod as public;
    }
}
```

### b) Traits with Abstract Methods

A trait can define `abstract` methods. This forces any class using the trait to provide its own implementation of that method, ensuring that the trait has the dependencies it needs to function correctly.

**Example: The `AuthorizesRequests` Trait**

This is the pattern from `PLAN.md`. The trait provides the `authorize` helper method, but it depends on the using class to provide implementations for `getAuthenticatedUser()` and the `$gate` property.

```php
// The trait requires the using class to implement this method.
abstract protected function getAuthenticatedUser(): PolicyInformationPoint;

// The trait requires the using class to have this property.
protected AuthorizationGate $gate;

trait AuthorizesRequests
{
    public function authorize(string $action, mixed ...$subjects): void
    {
        $currentUser = $this->getAuthenticatedUser();

        $context = new PolicyContext(
            actor: $currentUser,
            subjects: $subjects
        );

        $this->gate->authorize($action, $context);
    }
}

class BaseController
{
    use AuthorizesRequests;

    protected AuthorizationGate $gate;

    public function __construct(AuthorizationGate $gate)
    {
        $this->gate = $gate; // Fulfill the property requirement
    }

    // Fulfill the abstract method requirement
    protected function getAuthenticatedUser(): PolicyInformationPoint
    {
        // Framework-specific logic to get the current user
        return new User(id: 123, roles: ['editor']);
    }
}
```
This pattern allows the trait to provide concrete logic (`authorize`) while still depending on the specific context of the class that uses it.
