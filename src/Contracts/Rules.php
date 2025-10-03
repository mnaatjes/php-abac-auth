<?php

    namespace mnaatjes\ABAC\Contracts;
    use mnaatjes\ABAC\Support\ExpressionFactory;

    /**
     * Rules
     * 
     * @package mnaatjes\ABAC\Contracts
     * @version 1.0
     * @since 1.0
     */
    final readonly class Rules {

        private array $expressions;
        
        /**-------------------------------------------------------------------------*/
        /**
         * Rules
         * 
         * @param string $condition
         * @param array $expressions
         * @return void
         */
        /**-------------------------------------------------------------------------*/
        public function __construct(
            private string $condition,
            array $expressions
        ){
            // Determine Properties of each Expression
            $results = [];

            // Loop and Make
            foreach($expressions as $expression){
                // Make Expression Objects
                // Push to results array
                $results[] = ExpressionFactory::make($expression);
            }
            
            // Set Expressions Array
            $this->expressions = $results;
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Get Condition
         * 
         * @return string
         */
        /**-------------------------------------------------------------------------*/
        public function getCondition(): string{return $this->condition;}
        /**-------------------------------------------------------------------------*/
        /**
         * Get Expressions
         * 
         * @return array
         */
        /**-------------------------------------------------------------------------*/
        public function getExpressions(): array{return $this->expressions;}

        /**-------------------------------------------------------------------------*/
        /**
         * toArray
         * 
         * @return array
         */
        /**-------------------------------------------------------------------------*/
        public function toArray(): array{
            return [
                "condition"     => $this->getCondition(),
                "expressions"   => array_map(function($exp){
                    return $exp->toArray();
                }, $this->getExpressions())
            ];
        }
    }

?>