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
    use mnaatjes\ABAC\Tests\Unit\Contracts\Expressions\UnaryExpressionTest;

    $test = new UnaryExpressionTest('test');
    var_dump($test);
?>