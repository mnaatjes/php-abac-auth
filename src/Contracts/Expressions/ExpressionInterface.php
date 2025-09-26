<?php

    namespace mnaatjes\ABAC\Contracts\Expressions;
    use mnaatjes\ABAC\Support\AttributeAccessor;
    use mnaatjes\ABAC\Contracts\PolicyContext;

    interface ExpressionInterface {
        
        public function evaluate(PolicyContext $context, AttributeAccessor $accessor): bool;
    }
?>