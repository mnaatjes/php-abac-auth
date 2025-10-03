<?php

    namespace mnaatjes\ABAC\Support;
    use mnaatjes\ABAC\Foundation\PIP;
use ReflectionClass;
use ReflectionProperty;

    class AttributeAccessor {

        /**-------------------------------------------------------------------------*/
        /**-------------------------------------------------------------------------*/
        public function getValue(PIP $pip, string $attributeName, mixed $default=NULL): mixed{
            // Form get/is method from attribute name
            // Form Getter method
            $getter = $this->resolveMethod($attributeName);

            // Form Is Method
            $isMethod = $this->resolveMethod($attributeName, "is");

            // Form Extract method
            $extract = $attributeName;

            // Evaluate Get/Is Methods and return
            // Evaluate and Resolve Getter
            if(method_exists($pip, $getter) && is_callable([$pip, $getter])){
                return $pip->$getter();
            }

            // Evaluate and Resolve IsMethod
            if(method_exists($pip, $isMethod) && is_callable([$pip, $isMethod])){
                return $pip->$isMethod();
            }

            // Evaluate and Resolve Extract Method
            if(method_exists($pip, $extract) && is_callable([$pip, $extract])){
                return $pip->$extract();
            }

            // Failed to Resolve a Method
            // Attempt direct Property access (unedited)
            if(property_exists($pip, $attributeName)){
                // Ensure property is not private
                $reflection = new ReflectionProperty($pip::class, $attributeName);
                
                // Evaluate
                if($reflection->isPublic()){
                    // Return attribute value
                    return $pip->$attributeName;
                } else {
                    // Cannot access value of property
                    // Property is either Protected or Private
                    return $default;
                }
            }

            // Attempt direct Property access (camelCase)
            $camelName = $this->toCamelCase($attributeName);
            if(property_exists($pip, $camelName)){
                // Ensure property is not private
                $reflection = new ReflectionProperty($pip::class, $camelName);
                
                // Evaluate
                if($reflection->isPublic()){
                    // Return attribute value
                    return $pip->$camelName;
                } else {
                    // Cannot access value of property
                    // Property is either Protected or Private
                    return $default;
                }
            }

            // Unable to resolve or access property value
            // Return Default
            return $default;
        }

        /**-------------------------------------------------------------------------*/
        /**-------------------------------------------------------------------------*/
        private function resolveMethod(string $attributeName, string $prefix="get"){
            // Add prefix
            // Convert to camelCase
            return $prefix . $this->toCamelCase($attributeName);
        }

        /**-------------------------------------------------------------------------*/
        /**-------------------------------------------------------------------------*/
        private function resolveProperty(string $attributeName){}

        /**-------------------------------------------------------------------------*/
        /**-------------------------------------------------------------------------*/
        private function toCamelCase(string $str){
            return ucfirst(str_replace(['_', '-'], '', ucwords($str, '_-')));
        }
    }
?>