<?php

    namespace mnaatjes\ABAC\Adapters\PolicyManagers;
    use mnaatjes\ABAC\Contracts\PolicyManager;
    use mnaatjes\ABAC\Contracts\Policy;

    abstract class FilePolicyManager implements PolicyManager {
        private ?array $cache=[];

        /**
         * @var int $cacheTTL Time To Live / Expires
         */
        private int $cacheTTL=60;

        /**
         * @var int $lastReadTS Last time cache was read
         */
        private int $lastReadTS=0;

        protected string $filepath;

        /**-------------------------------------------------------------------------*/
        /**
         * Constructor:
         * - Validates Filepath
         * - Pulls Policy Data from File
         * - Updates
         * 
         * @param string $filepath
         * @return void
         */
        /**-------------------------------------------------------------------------*/
        public function __construct(string $filepath){
            // Set Filepath
            $this->filepath = $filepath;

            // Update Cache
            $this->updateCache();
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Find all policies in cache containing the subject
         * 
         * @param string $name
         * 
         * @uses $this->getCache() to retrieve updated cache
         * @return array Returns an array of Policies by Subject
         */
        /**-------------------------------------------------------------------------*/
        public function findByName(string $name): ?Policy{
            foreach($this->getCache() as $policy){
                if($policy->getName() === $name){
                    return $policy;
                }
            }

            // Return Default
            return NULL;
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Find all policies in cache containing the Effect
         * 
         * @param string $effect
         * 
         * @uses $this->getCache() to retrieve updated cache
         * @return array Returns an array of Policies by Effect
         */
        /**-------------------------------------------------------------------------*/
        public function findByEffect(string $effect): array{
            // Enforce effect values
            if(($effect !== "permit") && ($effect !== "deny")){
                throw new \UnexpectedValueException("Effect must be either `permit` or `deny`");
            }

            // Return Reduced Array
            return array_reduce($this->getCache(), function($acc, $policy) use($effect){
                // Check if policy name equals $effect
                if($policy->getEffect() === $effect){
                    $acc[] = $policy;
                }
                // return accumulator
                return $acc;
            }) ?? [];
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Find all policies in cache containing the actor
         * 
         * @param string $actor
         * 
         * @uses $this->getCache() to retrieve updated cache
         * @return array Returns an array of Policies by actor
         */
        /**-------------------------------------------------------------------------*/
        public function findByActor(string $actor): array{
            // Pull Array of actors from Registry
            return array_reduce($this->getCache(), function($acc, $policy) use($actor){
                // pull actors from policy
                if($policy->hasActor($actor)){
                    $acc[] = $policy;
                }

                // Return acc
                return $acc;
            }, []);
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Find all policies in cache containing the subject
         * 
         * @param string $subject
         * 
         * @uses $this->getCache() to retrieve updated cache
         * @return array Returns an array of Policies by Subject
         */
        /**-------------------------------------------------------------------------*/
        public function findBySubject(string $subject): array{
            // Pull Array of subjects from Registry
            return array_reduce($this->getCache(), function($acc, $policy) use($subject){
                // pull actors from policy
                if($policy->hasSubject($subject)){
                    $acc[] = $policy;
                }

                // Return acc
                return $acc;
            }, []);
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Updates Cache
         * 
         * @return void
         */
        /**-------------------------------------------------------------------------*/
        private function updateCache(): void{
            /**
             * @var int $currTime Current unix timestamp
             */
            $currTime = time();

            // Check Cache and Timestamp
            if((empty($this->cache)) || (($currTime - $this->lastReadTS) > $this->cacheTTL)){
                // Update Cache
                $this->cache = $this->getFileData();

                // Update Last Read Timestamp
                $this->lastReadTS = $currTime;
            }
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Getter function that updates cache and then returns updated cache
         * 
         * @uses $this->updateCache()
         * @return array Array of Policies
         */
        /**-------------------------------------------------------------------------*/
        private function getCache(): array{
            // Check TTL and Update cache if needed
            $this->updateCache();

            // Return updated cache
            return $this->cache;
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Find all policies
         * 
         * @return array
         */
        /**-------------------------------------------------------------------------*/
        public function findAll(): array{return $this->getCache();}

        /**-------------------------------------------------------------------------*/
        /**
         * Insert \ Update Policy in Source File
         */
        /**-------------------------------------------------------------------------*/
        public function save(Policy $policy): void{
            throw new \Exception("Save() functionality not available!");
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Remove Policy from Cache and Source File
         */
        /**-------------------------------------------------------------------------*/
        public function delete(string $policyName): bool{
            throw new \Exception("Delete() functionality not available!");
            return false;
        }

        /**-------------------------------------------------------------------------*/
        /**
         * @abstract
         * Logic for pulling data from file
         */
        /**-------------------------------------------------------------------------*/
        protected abstract function getFileData(): array;
    }

?>