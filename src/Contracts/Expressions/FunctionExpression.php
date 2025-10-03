<?php

    namespace mnaatjes\ABAC\Contracts\Expressions;
    use mnaatjes\ABAC\Contracts\Expressions\ExpressionInterface;
    use mnaatjes\ABAC\Support\AttributeAccessor;
    use mnaatjes\ABAC\Contracts\PolicyContext;
    use mnaatjes\ABAC\Contracts\Attribute;
    use mnaatjes\ABAC\Support\FunctionRegistry;

    /**
     * FunctionExpression
     * 
     * @package mnaatjes\ABAC\Contracts\Expressions
     * @version 1.0
     * @since 1.0
     * @category Expressions
     * 
     */
    class FunctionExpression implements ExpressionInterface {
        /**-------------------------------------------------------------------------*/
        /**
         * Constructor
         * 
         * @param string $functionName - Name of function to be executed
         * @param array<string|Attribute> $arguments - Array of Attributes or literals for the function
         * @param FunctionRegistry $registry
         * @throws \InvalidArgumentException
         */
        /**-------------------------------------------------------------------------*/
        public function __construct(
            /**
             * @var string $functionName - Name of function to be executed
             */
            private string $functionName,
            /**
             * @var array<string|Attribute> - Array of Attributes or literals for the function
             */
            private array $arguments,
            /**
             * @var FunctionRegistry
             */
            private FunctionRegistry $registry
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
            // Resolve All Arguments into Simple Values
            $arguments = [];
            foreach($this->arguments as $argument){
                // Check if Attribute instance and get value
                if($argument instanceof Attribute){
                    $arguments[] = $argument->getValue($context, $accessor, $default);
                } else {
                    // Argument is literal
                    $arguments[] = $argument;
                }
            }

            // Deligate execution to FunctionRegistry
            // Capture in Try/Catch for Errors
            // Return default if exception
            try {
                // Attempt Execution
                return $this->registry->execute($this->getFunctionName(), $arguments, $default);

            } catch(\Exception $e){
                // Return Default
                return $default;
            }
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Get Function Name
         * 
         * @return string
         */
        /**-------------------------------------------------------------------------*/
        public function getFunctionName(){return $this->functionName;}

        /**-------------------------------------------------------------------------*/
        /**
         * Get Arguments
         * 
         * @return array
         */
        /**-------------------------------------------------------------------------*/
        public function getArguments(){return $this->arguments;}

        /**-------------------------------------------------------------------------*/
        /**
         * Convert to Array
         * 
         * @return array
         */
        /**-------------------------------------------------------------------------*/
        public function toArray(){
            return [
                "functionName"  => $this->getFunctionName(),
                "arguments"     => $this->getArguments()
            ];
        }
    }
?>