<?php

    namespace mnaatjes\ABAC\Contracts;
    use mnaatjes\ABAC\Contracts\PolicyContext;
    use mnaatjes\ABAC\Support\AttributeAccessor;

    /**
     * Attribute
     * 
     * An Attribute is a property of an Actor, Subject, Environment, or Literal
     * 
     * @package mnaatjes\ABAC\Contracts
     * @version 1.0
     * @since 1.0
     * @category Attributes
     * 
     */
    final class Attribute {
        /**-------------------------------------------------------------------------*/
        /**
         * Constructor
         * 
         * @param string $entity - Actor, Subject, Environment, or Literal
         * @param string $name - Name of the attribute property: e..g "id"
         * @param mixed $literal - Explicitly declared literal value
         * 
         */
        /**-------------------------------------------------------------------------*/
        public function __construct(
            /**
             * @var string $entity - Actor, Subject, Environment, or Literal
             */
            private ?string $entity,
            /**
             * @var string $name - Name of the attribute property: e..g "id"
             */
            private ?string $name=NULL,
            /**
             * @var mixed $literal - Explicitly declared literal value
             */
            private mixed $literal=NULL
        ){}

        /**-------------------------------------------------------------------------*/
        /**
         * Get Entity
         * 
         * @return string
         */
        /**-------------------------------------------------------------------------*/
        public function getEntity(){return $this->entity;}

        /**-------------------------------------------------------------------------*/
        /**
         * Get Name
         * 
         * @return string
         */
        /**-------------------------------------------------------------------------*/
        public function getName(){return $this->name;}

        /**-------------------------------------------------------------------------*/
        /**
         * Get Literal
         * 
         * @return mixed
         */
        /**-------------------------------------------------------------------------*/
        public function getLiteral(){return $this->literal;}

        /**-------------------------------------------------------------------------*/
        /**
         * Get Value
         * 
         * @param PolicyContext $context
         * @param AttributeAccessor $accessor
         * @param mixed $default
         * @return mixed
         */
        /**-------------------------------------------------------------------------*/
        public function getValue(PolicyContext $context, AttributeAccessor $accessor, mixed $default): mixed{
            // Resolve and return the value of the Attribute
            return match($this->getEntity()){
                // Literal: Just return value
                'literal' => $this->getLiteral(),

                // Actor: Use accessor
                'actor' => $accessor->getValue($context->getActor(), $this->getName(), $default),

                // Subject: Use Accessor
                'subject' => $this->getSubjectValue($context, $accessor, $default),

                // Environment
                // TODO: Figure out multiple environments
                // TODO: Figure out keys of Environment rules
                'environment' => $context->getEnvironments()[$this->getName()] ?? $default,

                // Default Condition
                default => $default
            };
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Get Subject Value
         * 
         * @param PolicyContext $context
         * @param AttributeAccessor $accessor
         * @param mixed $default
         * @return mixed
         * 
         */
        /**-------------------------------------------------------------------------*/
        private function getSubjectValue(PolicyContext $context, AttributeAccessor $accessor, mixed $default): mixed{
            // Declare Subject and AttributeName
            $subject = NULL;
            $attributeName = NULL;

            // Check / Enforce dot-notation for subject_attribute property for multiple subjects
            if(!str_contains($this->getName(), ".")){
                // Attribute name is not dot-notation
                // Verify that there is only one subject
                if(count($context->getSubjects()) === 1){
                    // Set subjectClassName to classname of first subject
                    $subject = $context->getSubjects()[0];

                    // Set subjectAttributeName to attribute name
                    $attributeName = $this->getName();

                } else {
                    // Cannot reliably resolve subject
                    // Return Default
                    return $default;
                }

            } else{
                // Parse dot-notation into target classname and attribute name
                [$subjectClassName, $attributeName] = explode(".", $this->getName());

                // Get subject object by classname
                $subject = $context->getSubjectByClassname($subjectClassName);
            }

            // Check that class exists
            // Return attribute value
            if($subject){
                return $accessor->getValue($subject, $attributeName);
            }

            // Return Default
            return $default;
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Get Array Representation
         * 
         * @return array
         */
        /**-------------------------------------------------------------------------*/
        public function toArray(){
            return [
                "entity"    => $this->getEntity(),
                "name"      => $this->getName(),
                "literal"   => $this->getLiteral()
            ];
        }
    }
?>