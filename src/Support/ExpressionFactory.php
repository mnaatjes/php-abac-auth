<?php

    namespace mnaatjes\ABAC\Support;
    use mnaatjes\ABAC\Contracts\Expressions\BinaryExpression;
    use mnaatjes\ABAC\Contracts\Expressions\UnaryExpression;
    use mnaatjes\ABAC\Contracts\Attribute;

    /**
     * Expression Factory
     * 
     * @package ABAC
     * @subpackage Support
     * @version 1.0
     * @since 1.0
     * @category Support
     * @copyright (c) 2025 Michael Naatjes
     * @license MIT
     * @link https://github.com/mnaatjes/abac-auth
     * 
     * The Expression Factory is responsible for creating Expression Objects
     * 
     * Expressions to Support:
     * 
     * - Unary Expression: 
     *      -> Operates on a Single value
     *      -> Contains 2 properties
     * 
     * - Binary Expression: 
     *      -> Comparison of 3 properties
     *      -> leftEntity, operator, rightEntity
     * 
     * - Function Expression:
     *      -> Evaluates a custom function with one-or-more arguments
     *      -> startsWith(entityA.attribute, attributeValue), isBetween(env.Attribute, attributeValue)
     *      -> Contains a "function" property string and an "argumetns" property array of attributes and values
     */
    class ExpressionFactory {

        /**
         * @var array $ATTRIBUTE_ENTITIES
         * - Array of Attribute Entities
         * - Maps Attribute Entity Names to Attribute Entity Class
         */
        private const ATTRIBUTE_ENTITIES=[
            "actor"         => "actor_attribute",
            "subject"       => "subject_attribute",
            "environment"   => "environment_attribute",
        ];

        /**-------------------------------------------------------------------------*/
        /**
         * Private Constructor
         */
        /**-------------------------------------------------------------------------*/
        private function __construct(){}


        /**-------------------------------------------------------------------------*/
        /**
         * Make Expression Object
         * 
         * @param array $expression
         * @return Expression
         */
        /**-------------------------------------------------------------------------*/
        public static function make(array $expression){
            /**
             * @var int $count - Count number of properties within expression
             */
            $count = count($expression);


            // Determine Cases
            if($count === 2){
                // Unary Expression OR Function Expression
                // Check Property Names
                if(isset($expression["function"]) && (isset($expression["argument"]) && is_array($expression["argument"]))){
                    // Function Expression


                } else if(isset($expression["operator"])){
                    // Unary Expression
                    // Use Maker Method and Return new Expression Object
                    return self::createUnaryExpression($expression);

                } else {
                    // Failure Condition
                    // No valid Expression Object will contain another Format
                    //throw new \Exception("Invalid Property Count / Properties for Expression!");
                }

            } else if ($count === 3){
                // Binary Expression
                // Use Maker Method and Return new Expression Object
                return self::createBinaryExpression($expression);

            } else {
                // Complete Failure Condition
                throw new \Exception("Total Failure Condition. Unable to resolve Expression from Policy");
            }
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Determine if Expression is a Literal Entity
         * 
         * @param array $keys - Array of Expression Keys
         * @return bool
         */
        /**-------------------------------------------------------------------------*/
        private static function isLiteralEntity(array $keys): bool{
            // Cycle and check for _attribute suffix
            foreach($keys as $key){
                if(str_ends_with($key, "_attribute")){
                    // Attribute Property Exists
                    return false;
                }
            }
            // Return default
            return true;
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Extract Entity from Expression
         * 
         * @param array $keys - Array of Expression Keys
         * @return string
         */
        /**-------------------------------------------------------------------------*/
        private static function extractEntity(array $keys): string{
            // Cycle through keys and return entity if matched
            foreach($keys as $key){
                $entity = array_search($key, self::ATTRIBUTE_ENTITIES);
                if($entity !== false){
                    return $entity;
                }
            }        
            // Cycle completed with no resolution
            throw new \Exception("Unable to resolve Entity from Expression!");
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Create Unary Expression
         * 
         * @param array $expression
         * @return UnaryExpression
         * 
         */
        /**-------------------------------------------------------------------------*/
        private static function createUnaryExpression(array $expression): UnaryExpression{
            // Catch Operator Property
            $operator = $expression["operator"];

            // Default Properties
            $name           = NULL;
            $entity         = NULL;
            $literalValue   = NULL;

            // Determine if literal
            if(self::isLiteralEntity(array_keys($expression))){
                // Entity is a literal
                // Define Entity
                $entity = "literal";
                var_dump($expression);

                // Verify "value" property exists
                if(!isset($expression["value"])){
                    throw new \Exception("Unary Expression with 'literal' entity is missing 'value' property!");
                }

                // Define Value
                $literalValue = $expression["value"];

            } else {
                // Entity assigned to an attribute
                $entity = self::extractEntity(array_keys($expression));
                $name   = $expression[$entity . "_attribute"];
            }

            // Generate Attribute
            $attribute = new Attribute(
                entity: $entity,
                name: $name,
                literal: $literalValue
            );

            // return new Expression
            return new UnaryExpression(
                operator: $operator,
                operand: $attribute
            );
        }

        /**
         * Possible Expression Patterns:
         * 
         * - Attribute vs Literal Value
         * - Attribute vs Attribute
         */
        private static function createBinaryExpression(array $expression): BinaryExpression{
            /**
             * @var int $attributeCount - Number of properties with suffix "_attribute"
             */
            $attributeCount = array_reduce(array_keys($expression), function($count, $property){
                if(str_ends_with($property, "_attribute")){
                    $count++;
                }
                return $count;
            }, 0);

            var_dump($attributeCount);

            return new BinaryExpression();
        }
    }

?>