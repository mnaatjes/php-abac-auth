<?php

    namespace mnaatjes\ABAC\Contracts\Expressions;
    use mnaatjes\ABAC\Contracts\Expressions\ExpressionInterface;
    use mnaatjes\ABAC\Support\AttributeAccessor;
    use mnaatjes\ABAC\Contracts\PolicyContext;
    use mnaatjes\ABAC\Contracts\Attribute;

    class BinaryExpression implements ExpressionInterface {
        public function __construct(
            private Attribute $leftHand,
            private string $operator,
            private mixed $rightHand
        ){}
        public function evaluate(PolicyContext $context, AttributeAccessor $accessor): bool{return false;}
    }
?>