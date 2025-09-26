<?php

    namespace mnaatjes\ABAC\Contracts\Expressions;
    use mnaatjes\ABAC\Contracts\Expressions\ExpressionInterface;
    use mnaatjes\ABAC\Support\AttributeAccessor;
    use mnaatjes\ABAC\Contracts\PolicyContext;

    class FunctionExpression implements ExpressionInterface {
        public function __construct(
            /**
             * @var string $functionName - Name of function to be executed
             */
            private string $functionName,
            /**
             * @var array<string|Attribute> - Array of Attributes or literals for the function
             */
            private array $arguments
        ){}
        public function evaluate(PolicyContext $context, AttributeAccessor $accessor): bool{return false;}
    }
?>