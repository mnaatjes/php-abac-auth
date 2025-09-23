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
use mnaatjes\ABAC\Tests\DataObjects\Category;
    use mnaatjes\ABAC\Tests\Policies\Judge;
    use mnaatjes\ABAC\Gate;

    // Collect data

    // Load
    $fp = "Data/config.php";
    $loadFile = function() use($fp){return require $fp;};

    // Make Subjects
    $mkSubjects = function($loadFile){
        $data = [];
        foreach($loadFile() as $name => $desc){
            $data[] = new Category($name, $desc);
        }
        return $data;
    };

    // Perform schema validation
    
    // Declare Gate with PDP
    $gate = new Gate(
        new Judge()
    );

    // Perform authorization
    $gate->authorize("continue", new PolicyContext(
        new Category("apple", "failure condition"),
        $mkSubjects($loadFile),
        ["home"]
    ));

    // Authorization was successful
    var_dump("Success!");
?>