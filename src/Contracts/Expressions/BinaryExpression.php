<?php

    namespace mnaatjes\ABAC\Contracts\Expressions;
    use mnaatjes\ABAC\Contracts\Expressions\ExpressionInterface;
    use mnaatjes\ABAC\Support\AttributeAccessor;
    use mnaatjes\ABAC\Contracts\PolicyContext;

    class BinaryExpression implements ExpressionInterface {
        public function evaluate(PolicyContext $context, AttributeAccessor $accessor): bool{return false;}
    }
?>