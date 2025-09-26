<?php

    namespace mnaatjes\ABAC\Adapters\PolicyManagers;
    use mnaatjes\ABAC\Adapters\PolicyManagers\FilePolicyManager;
    use mnaatjes\ABAC\Contracts\Policy;

    class JSONPolicyManager extends FilePolicyManager {
        
        /**-------------------------------------------------------------------------*/
        /**
         * Capture Policy File Data and return it
         * 
         * @uses $this->filepath
         * @return array
         */
        /**-------------------------------------------------------------------------*/
        protected function getFileData(): array{

            /**
             * String of json file content
             * @var string $content
             */
            $content = file_get_contents($this->filepath);
            if($content === false){
                throw new \Exception("Unable to get contents of Policy File: " . $this->filepath);
            }

            /**
             * Assoc. Array of JSON data
             * @var array $data
             */
            $data = json_decode($content, true);

            if(json_last_error() !== JSON_ERROR_NONE){
                throw new \Exception("Unable to parse JSON data in Policy File: " . $this->filepath);
            }

            // Map Content and Return
            return array_map(function($policy){
                return new Policy(
                    name: $policy["name"],
                    effect: $policy["effect"],
                    actors: $policy["actors"],
                    actions: $policy["actions"],
                    subjects: $policy["subjects"],
                    rules: $policy["rules"],
                    description: $policy["description"],
                );
            }, $data["policies"]);
        }
    }

?>