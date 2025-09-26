<?php

    namespace mnaatjes\ABAC\Support;
    use mnaatjes\ABAC\Contracts\Expressions\BinaryExpression;
    use mnaatjes\ABAC\Contracts\Expressions\UnaryExpression;
    use mnaatjes\ABAC\Contracts\Attribute;
use mnaatjes\ABAC\Contracts\Expressions\FunctionExpression;

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
                if(isset($expression["function"])){
                    // Create and Return Function Expression
                    return self::createFunctionExpression($expression);

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
        private static function extractEntityFromKeys(array $keys): string{
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
         * @param string $property - Property name of entity
         * @return string Entity string
         */
        /**-------------------------------------------------------------------------*/
        private static function extractEntityFromProperty(string $property): string{
            // Find length
            $pos = strpos($property, "_attribute");
            // Return string
            return (substr($property, 0, $pos));
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
                $entity = self::extractEntityFromKeys(array_keys($expression));
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

        /**-------------------------------------------------------------------------*/
        /**
         * Possible Expression Patterns:
         * 
         * - Attribute vs Literal Value
         * - Attribute vs Attribute
         * 
         * @param array $expression - Expression array from Policy
         * @return BinaryExpression
         */
        /**-------------------------------------------------------------------------*/
        private static function createBinaryExpression(array $expression): BinaryExpression{
            // Verify Operator
            if(!isset($expression["operator"])){
                throw new \Exception("Binary Expression is missing operator!");
            }

            /**
             * @var string $operator
             */
            $operator = $expression["operator"];

            /**
             * @var array $attributes - Collected attributes from Expression
             */
            $attributes = [];

            foreach($expression as $property => $definition){
                // Check for attribute
                // Create attribute object if exists
                if(str_ends_with($property, "_attribute")){
                    // Determine Entity
                    $entity = self::extractEntityFromProperty($property);

                    // Determine Name
                    $name = $definition;

                    // Assign Literal Value
                    $literalValue = NULL;

                    // Push to container array
                    $attributes[] = new Attribute(
                        entity: $entity,
                        name: $name,
                        literal: $literalValue
                    );
                }
            }

            // Count attributes to determine Binary Expression Pattern
            if(count($attributes) === 2){
                // Attribute vs Attribute Pattern
                // Assemble and return Binary Expression
                return new BinaryExpression(
                    leftHand: $attributes[0],
                    operator: $operator,
                    rightHand: $attributes[1]
                );

            } else if(count($attributes) === 1){
                // Attribute vs Literal
                // Check for literal
                if(!isset($expression["value"])){
                    throw new \Exception("Binary expression is missing literal Right Hand Argument!");
                }

                /**
                 * @var mixed $literal - "Value" property of Binary Expression
                 */
                $literal = $expression["value"];

                // Assemble and return Binary Expression
                return new BinaryExpression(
                    leftHand: $attributes[0],
                    operator: $operator,
                    rightHand: $literal
                );

            } else {
                // Failure Condition
                // Cannot Resolve
                throw new \Exception("Unable to resolve Binary Expression! Please review JSON properties");
            }
        }

        private static function createFunctionExpression(array $expression): FunctionExpression{
            // Verify and Assign Function String
            if(!isset($expression["function"])){
                throw new \Exception("Function Expression missing 'Function' property!");
            }

            /**
             * @var string $functionName
             */
            $functionName = $expression["function"];

            // Verify Arguments Property Exists
            if(!isset($expression["arguments"])){
                throw new \Exception("Function Expression is missing 'Arguments' property!");
            }

            /**
             * @var array<string|Attribute> $arguments - Arguments collected from Expression
             */
            $arguments = [];

            foreach($expression as $property=>$description){
                // Check for Attributes
                if(str_ends_with($property, "_attribute")){
                    // Evaluate and Store Attribute

                    // Push to arguments
                    $arguments[] = new Attribute(
                        entity: self::extractEntityFromProperty($property),
                        name: $description,
                        literal: NULL
                    );
                }

                // Check for Literals
                if($property === "value"){
                    // Push to arguments
                    $arguments[] = $description;
                }
            }

            // Return Expression Object
            return new FunctionExpression(
                functionName: $functionName,
                arguments: []
            );
        }
    }

?>