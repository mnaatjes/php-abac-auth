<?php

    namespace mnaatjes\ABAC\Support;

    class FunctionRegistry {

        private static ?FunctionRegistry $instance = null;
        private array $functions = [];

        /**-------------------------------------------------------------------------*/
        /**
         * Constructor
         * 
         * @uses $this->registerBuiltIns()
         */
        /**-------------------------------------------------------------------------*/
        private function __construct(){
            // Register built-ins
            $this->registerBuiltIns();
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Get Instance
         * 
         * @return FunctionRegistry
         */
        /**-------------------------------------------------------------------------*/
        public static function getinstance(): FunctionRegistry{
            // Check if instance exists
            if(!isset(self::$instance)){
                // Create new instance
                self::$instance = new self();
            }

            // Return existing instance
            return self::$instance;
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Register built-in functions
         * 
         * @return void
         */
        /**-------------------------------------------------------------------------*/
        private function registerBuiltIns(): void{
            // Register built-ins
            $this->register('startsWith', 'str_starts_with');
            $this->register('endsWith', 'str_ends_with');
            $this->register('contains', 'str_contains');
            $this->register('length', 'strlen');
            $this->register('inArray', 'in_array');
            $this->register('isEmpty', function($value): bool{
                return empty($value);
            });
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Register a function
         * 
         * @param string $key
         * @param callable|string $function
         * @return void
         * @throws \InvalidArgumentException
         */
        /**-------------------------------------------------------------------------*/
        public function register(string $key, callable|string $function): void{
            // Check if function already registered
            if(isset($this->functions[$key])){
                throw new \InvalidArgumentException("Function: " . $key . " already registered");
            }
            // Check if function exists
            if(!is_callable($function)){
                throw new \InvalidArgumentException("Function: " . $key . " must be callable");
            }

            // Register in array
            $this->functions[$key] = $function;
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Execute a function from the registry
         * 
         * @param string $key
         * @param array $arguments
         * @return bool
         */
        /**-------------------------------------------------------------------------*/
        public function execute(string $key, array $arguments): bool{
            // Verify function exists
            if(!isset($this->functions[$key])){
                throw new \InvalidArgumentException("Function: " . $key . " does not exist");
            }

            // Execute Function
            return call_user_func_array($this->functions[$key], $arguments);
        }
    }
?>