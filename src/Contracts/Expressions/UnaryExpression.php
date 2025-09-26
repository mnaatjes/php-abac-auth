<?php

    namespace mnaatjes\ABAC\Contracts\Expressions;
    use mnaatjes\ABAC\Contracts\Expressions\ExpressionInterface;
    use mnaatjes\ABAC\Support\AttributeAccessor;
    use mnaatjes\ABAC\Contracts\PolicyContext;
    use mnaatjes\ABAC\Contracts\Attribute;

    class UnaryExpression implements ExpressionInterface {

        public function __construct(
            private string $operator,
            private Attribute $operand
        ){}

        public function evaluate(PolicyContext $context, AttributeAccessor $accessor): bool{return false;}
    }
?>