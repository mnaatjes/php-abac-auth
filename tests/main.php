<?php

    /**
     * @file abac-auth/tests/oop_main.php
     * @version 0.1.0
     * @package abac-auth
     * @license MIT
     */

    // require autoloader
    require_once __DIR__ . '/../vendor/autoload.php';

    use mnaatjes\ABAC\ABAC;
    use mnaatjes\ABAC\Contracts\PolicyContext;

    // Implement Factory
    $pap = ABAC::createAdmin(__DIR__ . '/Data/policies.json');
    $pep = ABAC::createRuntime(__DIR__ . '/Data/policies.json');

    $pep->enforce("edit-post", new PolicyContext(
        // Actor
        "", 
        // Subjects
        [],
        // Environment
        []
    ));

?>