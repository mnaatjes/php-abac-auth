<?php

    namespace mnaatjes\ABAC\Contracts\Rules;
    use mnaatjes\ABAC\Support\ExpressionFactory;

    final readonly class Rule {

        private array $expressions;
        
        /**-------------------------------------------------------------------------*/
        /**
         * 
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
         * 
         */
        /**-------------------------------------------------------------------------*/
        public function getCondition(){return $this->condition;}
        /**-------------------------------------------------------------------------*/
        /**
         * 
         */
        /**-------------------------------------------------------------------------*/
        public function getExpressions(): array{return $this->expressions;}
    }

?>