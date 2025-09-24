<?php

    namespace mnaatjes\ABAC;
    use mnaatjes\ABAC\Adapters\PolicyManagers\JSONPolicyManager;
    use mnaatjes\ABAC\Adapters\PolicyManagers\YAMLPolicyManager;
    use mnaatjes\ABAC\Adapters\PolicyManagers\DBPolicyManager;
    use mnaatjes\ABAC\Contracts\PolicyManager;
    use mnaatjes\ABAC\Foundation\PAP;
    use mnaatjes\ABAC\Foundation\PRP;
    use mnaatjes\ABAC\Foundation\PDP;
    use mnaatjes\ABAC\Foundation\PEP;

    final class ABAC {

        /**
         * @private
         * Unused
         */
        private function __construct(){}

        /**-------------------------------------------------------------------------*/
        /**
         * Factory Method to generate an ABAC Administrator based on Data Structure of Policy Storage
         * 
         * @static
         * @return PAP
         */
        /**-------------------------------------------------------------------------*/
        public static function createAdmin(string|\PDO $arg): PAP{
            /**
             * @var string $type Type of runtime environment
             */
            $type = ($arg instanceof \PDO) ? "db" : (self::isValidPath($arg) ? strtolower(pathinfo($arg, PATHINFO_EXTENSION)) : NULL);

            // Return Instance of Policy Administration Point
            return new PAP(
                // Instantiate Policy Retrieval Point
                // Inject Policy Manager Dependency
                self::matchPolicyManager($type, $arg)
            );
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Factory Method to generate an ABAC Enforcement Point based on Data Structure of Policy Storage
         * 
         * @static
         * @return PEP
         */
        /**-------------------------------------------------------------------------*/
        public static function createRuntime(string|\PDO $arg): PEP{
            /**
             * @var string $type Type of runtime environment
             */
            $type = ($arg instanceof \PDO) ? "db" : (self::isValidPath($arg) ? strtolower(pathinfo($arg, PATHINFO_EXTENSION)) : NULL);

            // Return Instance
            // Return Policy Enforcement Point
            return new PEP(
                // Instantiate Policy Decision Point
                new PDP(
                    // Instantiate Policy Retrieval Point
                    // Inject Policy Manager Dependency
                    new PRP(self::matchPolicyManager($type, $arg))
                )
            );
        }

        /**-------------------------------------------------------------------------*/
        /**
         * 
         */
        /**-------------------------------------------------------------------------*/
        private static function matchPolicyManager(string $type, string|\PDO $arg): PolicyManager{
            // Route to desired admin by file extension
            return match($type){
                "db" => new DBPolicyManager($arg),
                "yml" => new YAMLPolicyManager($arg),
                "json" => new JSONPolicyManager($arg),
                default => throw new \Exception("Unsupported source type or extension: " . $arg)
            };
        }

        /**-------------------------------------------------------------------------*/
        /**
         * 
         */
        /**-------------------------------------------------------------------------*/
        private static function isValidPath(string $filepath){
            // Throw Exception on failure
            self::validateFilepath($filepath);

            // Passed validation
            // Return true
            return true;
        }
        
        /**-------------------------------------------------------------------------*/
        /**
         * @throws Exception
         */
        /**-------------------------------------------------------------------------*/
        private static function validateFilepath(string $filepath){
            // Validate Filepath
            if(!file_exists($filepath)){
                throw new \Exception("Policy file not found at path: " . $filepath);
            }

            if(!is_readable($filepath)){
                throw new \Exception("Cannot read Policy file at path: " . $filepath);
            }

            if(!is_file($filepath)){
                throw new \Exception("Filepath: " .$filepath. " does not point to a file!");
            }
        }

    }
?>