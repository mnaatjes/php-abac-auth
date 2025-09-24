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

## 5. Detailed Trait Examples

### a) The `Timestampable` Trait

This trait is a classic in ORMs. It automates the process of setting `created_at` and `updated_at` timestamps, which is a near-universal requirement for database records.

#### The Trait's Code

The trait itself provides the properties to hold the timestamps and the logic to update them. It doesn't know *when* it should run, so it provides a `protected` method that the using class can call from its own `save()` method.

```php
<?php

trait Timestampable
{
    /** @var ?\DateTimeImmutable The timestamp when the record was created. */
    protected ?\DateTimeImmutable $createdAt = null;

    /** @var ?\DateTimeImmutable The timestamp when the record was last updated. */
    protected ?\DateTimeImmutable $updatedAt = null;

    /**
     * Updates the timestamps. Should be called when the model is saved.
     * If createdAt is not set, it sets both createdAt and updatedAt.
     * Otherwise, it only updates updatedAt.
     */
    protected function touchTimestamps(): void
    {
        $now = new \DateTimeImmutable();

        if ($this->createdAt === null) {
            $this->createdAt = $now;
        }

        $this->updatedAt = $now;
    }

    /**
     * Gets the creation timestamp.
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Gets the last update timestamp.
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
```

#### The Class Using the Trait

Here is a `Product` model that uses the `Timestampable` trait. Notice how its `save()` method calls the `touchTimestamps()` method provided by the trait.

```php
<?php

class Product
{
    // 1. The class "is now" timestampable.
    use Timestampable;

    public function __construct(
        public int $id,
        public string $name
    ) {
        echo "Product '{$this->name}' created in memory.\n";
    }

    /**
     * Simulates saving the model to a database.
     */
    public function save(): void
    {
        echo "Saving '{$this->name}'...\n";

        // 2. It calls the trait's method to handle the timestamp logic.
        $this->touchTimestamps();

        echo "Saved.\n";
    }
}
```

#### Example of Usage

This script shows the trait's behavior in action.

```php
// Helper function to format dates for display
function formatDate(?\DateTimeImmutable $date): string {
    return $date ? $date->format('Y-m-d H:i:s') : 'null';
}

// --- First Save (Create) ---
$product = new Product(101, 'Super Widget');

echo "Before first save:\n";
echo "  - createdAt: " . formatDate($product->getCreatedAt()) . "\n"; // null
echo "  - updatedAt: " . formatDate($product->getUpdatedAt()) . "\n"; // null
echo "--------------------\n";

$product->save(); // This calls touchTimestamps()

echo "\nAfter first save:\n";
echo "  - createdAt: " . formatDate($product->getCreatedAt()) . "\n"; // e.g., 2025-09-23 10:30:00
echo "  - updatedAt: " . formatDate($product->getUpdatedAt()) . "\n"; // e.g., 2025-09-23 10:30:00
echo "--------------------\n";


// --- Second Save (Update) ---
sleep(2); // Wait 2 seconds to see a different timestamp
$product->name = 'Super Widget Pro';
$product->save();

echo "\nAfter second save:\n";
echo "  - createdAt: " . formatDate($product->getCreatedAt()) . "\n"; // Stays the same
echo "  - updatedAt: " . formatDate($product->getUpdatedAt()) . "\n"; // Is now 2 seconds later
echo "--------------------\n";
```

### b) The `Serializable` (or `Arrayable`) Trait

This trait gives an object the ability to represent itself as an array. This is incredibly useful for API development, where you need to convert your internal objects to JSON. The best way to implement this is by using PHP's built-in `JsonSerializable` interface.

#### The Trait's Code

This trait provides the `jsonSerialize` method required by the `JsonSerializable` interface. It intelligently handles nested objects and arrays of objects that also use this trait.

```php
<?php

trait Serializable
{
    /**
     * Specify data which should be serialized to JSON.
     * This method is required by the JsonSerializable interface.
     */
    public function jsonSerialize(): array
    {
        $properties = get_object_vars($this);
        $output = [];

        foreach ($properties as $key => $value) {
            if ($value instanceof \JsonSerializable) {
                // If a property is another serializable object, recursively call its method.
                $output[$key] = $value->jsonSerialize();
            } elseif (is_array($value)) {
                // If a property is an array, process each item in the array.
                $output[$key] = $this->serializeArray($value);
            } else {
                $output[$key] = $value;
            }
        }
        return $output;
    }

    /**
     * Helper to recursively serialize items in an array.
     */
    private function serializeArray(array $array): array
    {
        $output = [];
        foreach ($array as $key => $item) {
            if ($item instanceof \JsonSerializable) {
                $output[$key] = $item->jsonSerialize();
            } elseif (is_array($item)) {
                $output[$key] = $this->serializeArray($item);
            } else {
                $output[$key] = $item;
            }
        }
        return $output;
    }
}
```

#### The Classes Using the Trait

Here we have two DTOs. `UserDTO` contains an `AddressDTO` and an array of `RoleDTO` objects. All of them use the `Serializable` trait and implement the `JsonSerializable` interface, making them "serializable".

```php
<?php

// The Address DTO
final readonly class AddressDTO implements \JsonSerializable
{
    use Serializable; // This class can be serialized.

    public function __construct(
        public string $street,
        public string $city,
    ) {}
}

// The Role DTO
final readonly class RoleDTO implements \JsonSerializable
{
    use Serializable; // This class can be serialized.

    public function __construct(
        public string $name
    ) {}
}

// The main User DTO
final readonly class UserDTO implements \JsonSerializable
{
    use Serializable; // This class can be serialized.

    /**
     * @param RoleDTO[] $roles
     */
    public function __construct(
        public int $id,
        public string $name,
        public AddressDTO $address, // A nested serializable object
        public array $roles        // An array of serializable objects
    ) {}
}
```

#### Example of Usage

This script shows how calling `json_encode()` on the top-level object triggers the serialization process all the way down.

```php
$address = new AddressDTO('123 Main St', 'Anytown');
$roles = [
    new RoleDTO('Admin'),
    new RoleDTO('Editor'),
];

$user = new UserDTO(42, 'John Doe', $address, $roles);

// Because our classes implement JsonSerializable, json_encode knows how to handle them.
// It will automatically call the `jsonSerialize` method provided by our trait.
$jsonOutput = json_encode($user, JSON_PRETTY_PRINT);

echo $jsonOutput;
```

**Output:**
```json
{
    "id": 42,
    "name": "John Doe",
    "address": {
        "street": "123 Main St",
        "city": "Anytown"
    },
    "roles": [
        {
            "name": "Admin"
        },
        {
            "name": "Editor"
        }
    ]
}
```

## 6. Common "Is-A" Trait Examples

In each case, the Trait adds a **behavior** to the class itself, making it an intrinsic part of what the class is or what it can do.

*   **`SoftDeletes`**
    *   **Used by:** ORM Models (`Post`, `User`).
    *   **"Is-a" Statement:** The `Post` model **is now** soft-deletable.
    *   **Functionality:** Intercepts `delete()` calls to set a `deleted_at` timestamp instead of truly deleting the record.

*   **`Sluggable`**
    *   **Used by:** ORM Models (`Article`, `Product`).
    *   **"Is-a" Statement:** The `Article` model **is** sluggable.
    *   **Functionality:** Automatically generates a URL-friendly "slug" (e.g., `my-awesome-post`) from a `title` property when the model is saved.

*   **`Notifiable`**
    *   **Used by:** Models that can receive notifications (`User`).
    *   **"Is-a" Statement:** The `User` model **can be** notified.
    *   **Functionality:** Adds a `notify()` method to the class, providing a consistent API for sending notifications related to that object.

*   **`Singleton`**
    *   **Used by:** Classes that must only have one instance (`Settings`, `Registry`).
    *   **"Is-a" Statement:** The `Settings` class **is** a singleton.
    *   **Functionality:** Encapsulates the entire singleton pattern (`getInstance()`, private constructor) to enforce that only one instance of the class can be created.

*   **`TracksDirtyAttributes`**
    *   **Used by:** Models or data objects that need to track changes.
    *   **"Is-a" Statement:** The `Configuration` object **can** track its changes.
    *   **Functionality:** Provides methods like `isDirty('property')` and `getOriginal('property')` to see what has changed on the object since it was loaded.

*   **`ForwardsCalls` (or `Macroable`)**
    *   **Used by:** Manager classes or Facades.
    *   **"Is-a" Statement:** The `CacheManager` **is** macroable; it **can be** extended at runtime.
    *   **Functionality:** Implements the `__call()` magic method to allow developers to add new methods to the class dynamically.

## 7. Decision Matrix: Trait vs. Dependency Injection

| Aspect | Trait | Dependency Injection (DI) |
| :--- | :--- | :--- |
| **Core Purpose** | **Code Reuse.** To add a shared set of methods directly to a class. | **Collaboration.** To provide a class with a service it needs to do its job. |
| **Relationship** | **"Is-a" / "Can-do".** The class itself gains the ability. *`MyController` can authorize requests.* | **"Has-a" / "Uses-a".** The class has a dependency that it uses. *`MyController` has a `Gate` it uses for authorization.* |
| **Coupling** | **Tighter.** The class is directly coupled to the trait's implementation. Swapping it out requires changing the class's `use` statement. | **Looser.** The class is coupled to an interface (an abstraction), not a concrete implementation. You can easily swap the dependency in the Service Container without touching the class. |
| **Testability** | **Harder.** You cannot easily "mock" a trait. You are testing the class with the trait's code effectively copied into it. | **Easier.** You can easily provide a "mock" or fake version of the dependency during testing, allowing you to test the class in isolation. |
| **Flexibility** | **Less Flexible.** Changing the functionality requires changing the trait itself, which affects every class that uses it. | **More Flexible.** You can provide different implementations of the same interface for different environments or scenarios. |
| **When to Use** | Helper methods, shortcuts, and self-contained behaviors like serialization. | External services, complex logic, or anything that interacts with infrastructure (database, filesystem, APIs). |
