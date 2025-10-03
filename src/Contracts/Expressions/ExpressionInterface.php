<?php

    namespace mnaatjes\ABAC\Contracts\Expressions;
    use mnaatjes\ABAC\Support\AttributeAccessor;
    use mnaatjes\ABAC\Contracts\PolicyContext;

    /**
     * Interface ExpressionInterface
     * 
     * @package mnaatjes\ABAC\Contracts
     * @version 1.0
     * @since 1.0
     * @category Expressions
     * @author M.Naatjes
     * 
     */
    interface ExpressionInterface {
        
        public function evaluate(PolicyContext $context, AttributeAccessor $accessor): bool;
    }
?>