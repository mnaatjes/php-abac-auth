<?php

    namespace mnaatjes\ABAC\Contracts\Expressions;
    use mnaatjes\ABAC\Contracts\Expressions\ExpressionInterface;
    use mnaatjes\ABAC\Support\AttributeAccessor;
    use mnaatjes\ABAC\Contracts\PolicyContext;
    use mnaatjes\ABAC\Contracts\Attribute;

    /**
     * Unary Expression
     * 
     * @package mnaatjes\ABAC\Contracts\Expressions
     * @version 1.0.0
     * @since 1.0.0
     * @category Expressions
     * 
     */
    class UnaryExpression implements ExpressionInterface {

        /**-------------------------------------------------------------------------*/
        /**
         * UnaryExpression Constructor
         * 
         * @param string $operator
         * @param Attribute $operand
         * 
         * @return void
         */
        /**-------------------------------------------------------------------------*/
        public function __construct(
            private string $operator,
            private Attribute $operand
        ){}

        /**-------------------------------------------------------------------------*/
        /**
         * Evaluate Expression
         * 
         * @param PolicyContext $context
         * @param AttributeAccessor $accessor
         * @return bool
         * 
         */
        /**-------------------------------------------------------------------------*/
        public function evaluate(PolicyContext $context, AttributeAccessor $accessor, mixed $default=NULL): bool{
            // Get Operand Value
            $operandValue = $this->operand->getValue($context, $accessor, $default);

            // Apply operator
            return match($this->operator){
                // Not
                "!", "not" => !$operandValue,

                // Empty
                "empty" => empty($operandValue),

                // Not Empty
                "!empty" => !empty($operandValue),

                // Exists
                "exists" => isset($operandValue),

                // Not Exists
                "!exists" => !isset($operandValue),

                // Null
                "null" => is_null($operandValue),

                // Not Null
                "!null" => !is_null($operandValue),

                // True
                "true" => $operandValue === true,

                // False
                "false" => $operandValue === false,

                // Default Case
                default => $default
            };
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Get Operator
         * 
         * @return string
         */
        /**-------------------------------------------------------------------------*/
        public function getOperator(): string{return $this->operator;}

        /**-------------------------------------------------------------------------*/
        /**
         * Get Operand
         * 
         * @return Attribute
         */
        /**-------------------------------------------------------------------------*/
        public function getOperand(){return $this->operand;}

        /**-------------------------------------------------------------------------*/
        /**
         * Convert Expression to Array
         * 
         * @return array
         */
        /**-------------------------------------------------------------------------*/
        public function toArray(){
            return [
                "operator"  => $this->getOperator(),
                "operand"   => $this->getOperand()->toArray()
            ];
        }
    }
?>