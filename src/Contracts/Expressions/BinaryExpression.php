<?php

    namespace mnaatjes\ABAC\Contracts\Expressions;
    use mnaatjes\ABAC\Contracts\Expressions\ExpressionInterface;
    use mnaatjes\ABAC\Support\AttributeAccessor;
    use mnaatjes\ABAC\Contracts\PolicyContext;
    use mnaatjes\ABAC\Contracts\Attribute;

    /**
     * BinaryExpression
     * 
     * @package mnaatjes\ABAC\Contracts\Expressions
     * @version 1.0
     * @since 1.0
     * @category Expressions
     * 
     */
    class BinaryExpression implements ExpressionInterface {

        /**-------------------------------------------------------------------------*/
        /**
         * Constructor
         * 
         * @param Attribute $leftHand
         * @param string $operator
         * @param mixed $rightHand
         * 
         */
        /**-------------------------------------------------------------------------*/
        public function __construct(
            private Attribute $leftHand,
            private string $operator,
            private mixed $rightHand
        ){}

        /**-------------------------------------------------------------------------*/
        /**
         * Evaluate Expression
         * 
         * @param PolicyContext $context
         * @param AttributeAccessor $accessor
         * @return bool
         * @throws \InvalidArgumentException
         * 
         */
        /**-------------------------------------------------------------------------*/
        public function evaluate(PolicyContext $context, AttributeAccessor $accessor, mixed $default=NULL): bool{
            // Find Left Value
            $leftValue = $this->leftHand->getValue($context, $accessor, $default);

            // Declare Right Hand Value
            $rightValue = NULL;

            // Find Right Value
            // Check if value is an Attribute Object Instance
            if($this->rightHand instanceof Attribute){
                $rightValue = $this->rightHand->getValue($context, $accessor, $default);
                
            } else {
                // Right Hand Value is NOT instance of Attribute
                // Get raw value
                $rightValue = $this->rightHand;
            }

            // Find Operator
            // Perform Evaluation
            return match($this->operator){
                // Equals
                "==", "equal", "equals", "eq" => $leftValue == $rightValue,

                // Identical
                "===", "identical", "seq" => $leftValue === $rightValue,

                // Not Equal
                "!=", "ne", "notEqual", "neq" => $leftValue != $rightValue,

                // Not Identical
                "!==", "sne", "sneq", "notIdentical" => $leftValue !== $rightValue,

                // Greater Than
                ">", "gt", "greater" => $leftValue > $rightValue,

                // Greater Than or Equal
                ">=", "gte" => $leftValue >= $rightValue,

                // Less Than
                "<", "lt", "less", "lessThan" => $leftValue < $rightValue,

                // Less Than or Equal
                "<=", "lte", => $leftValue <= $rightValue,


                // Default
                default => $default
            };
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Get Left Hand
         * 
         * @return Attribute
         */
        /**-------------------------------------------------------------------------*/
        public function getLeftHand(): Attribute{return $this->leftHand;}

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
         * Get Right Hand
         * 
         * @return mixed
         * 
         */
        /**-------------------------------------------------------------------------*/
        public function getRightHand(): mixed{return $this->rightHand;}

        /**-------------------------------------------------------------------------*/
        /**
         * Convert Expression to Array
         * 
         * @return array
         */
        /**-------------------------------------------------------------------------*/
        public function toArray(): array{
            return [
                "leftHand"  => $this->getLeftHand()->toArray(),
                "operator"  => $this->getOperator(),
                "righthand" => $this->getRightHand()
            ];
        }
    }
?>