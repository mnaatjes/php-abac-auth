<?php

    /**
     * @file abac-auth/tests/main.php
     * @version 0.1.0
     * @package abac-auth
     * @license MIT
     * @copyright (c) 2025 Michael Naatjes
     * @link https://github.com/mnaatjes/abac-auth
     * @since 0.1.0
     */


    // require autoloader
    require_once __DIR__ . '/../vendor/autoload.php';

    use mnaatjes\ABAC\Contracts\PolicyContext;
    use mnaatjes\ABAC\Contracts\PolicyInformationPoint;
    use mnaatjes\ABAC\Tests\DataObjects\Category;
    use mnaatjes\ABAC\Tests\Policies\Judge;
    use mnaatjes\ABAC\Gate;

    // Create Entity making the Request: Actor
    final readonly class actor implements PolicyInformationPoint {
        public function __construct(
            public readonly string $name,
            public readonly ?int $id=NULL,
            public readonly ?string $description=NULL
        ){}
    }

    // Declare Auth Gate
    $gate = new Gate(new Judge());

    // Try and Catch
    try {
        // Perform Authorization
        $gate->authorize(
            // Action
            "Action",
            new PolicyContext(
                // Actor
                new Actor("API"),
                // Subjects
                [new Category("ham", "Description...")],
                // Environment
                ["location" => "home"]
        ));
        
        // Debugging Message
        var_dump("Success!");

    } catch(\Exception $e){
        // Throw Exception
        var_dump("Failure! " . $e);
    }

?>