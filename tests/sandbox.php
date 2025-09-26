<?php
    /**
     * @file src/tests/sandbox.php
     */

    // require autoloader
    require_once __DIR__ . '/../vendor/autoload.php';

    use mnaatjes\ABAC\ABAC;
use mnaatjes\ABAC\Contracts\Expressions\BinaryExpression;
use mnaatjes\ABAC\Contracts\Expressions\FunctionExpression;
use mnaatjes\ABAC\Contracts\Expressions\UnaryExpression;
use mnaatjes\ABAC\Contracts\PolicyContext;
    use mnaatjes\ABAC\Foundation\PIP;
    use mnaatjes\ABAC\Support\ExpressionFactory;

    // Create ABAC Runtime
    $auth = ABAC::createRuntime(__DIR__ . "/Data/policies.json");

    // Get a single policy
    $admin = ABAC::createAdmin(__DIR__ . "/Data/policies.json");
    $expressions = array_reduce($admin->listAll(), function($acc, $policy){
        foreach($policy->getRules()["expressions"] as $expression){
            $acc[] = $expression;
        };
        return $acc;
    }, []);

    $results = [];

    // Loop and Make
    foreach($expressions as $expression){
        // Make Expression Objects
        // Push to results array
        $results[] = ExpressionFactory::make($expression);
    }
    
    // Check Type
    $countUnary  = 0;
    $countBinary = 0;
    $countFunc   = 0;
    foreach($results as $exp){
        if(is_a($exp, UnaryExpression::class)){
            $countUnary++;
        } else if(is_a($exp, BinaryExpression::class)){
            $countBinary++;
        } else if(is_a($exp, FunctionExpression::class)){
            $countFunc++;
        }
    }

    var_dump("Unary:     " . $countUnary);
    var_dump("Binary:   " . $countBinary);
    var_dump("Function:  " . $countFunc);
?>