<?php
    /**
     * @file src/tests/sandbox.php
     */

    // require autoloader
    require_once __DIR__ . '/../vendor/autoload.php';

    use mnaatjes\ABAC\ABAC;
    use mnaatjes\ABAC\Contracts\PolicyContext;
    use mnaatjes\ABAC\Tests\Adapters\User;
    use mnaatjes\ABAC\Tests\Adapters\Item;
    use mnaatjes\ABAC\Tests\Adapters\Post;

    // Create ABAC Runtime
    $auth = ABAC::createRuntime(__DIR__ . "/Data/policies.json");

    // Get a single policy
    $admin = ABAC::createAdmin(__DIR__ . "/Data/policies.json");
    
    $auth->enforce("view-post", new PolicyContext(
        // Actor
        new User("admin"),
        // Subjects
        [
            new Post(false)
        ],
        // Environment
        []
    ));
?>