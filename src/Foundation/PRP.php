<?php

    namespace mnaatjes\ABAC\Foundation;
    use mnaatjes\ABAC\Contracts\Policy;
    use mnaatjes\ABAC\Contracts\PolicyManager;
    use mnaatjes\ABAC\Contracts\PolicyContext;

    /**-------------------------------------------------------------------------*/
    /**
     * PRP: Policy Retrieval Point
     * 
     * The component that queries the YAML or JSON file (store) to retrieve policies.
     * - Called by the PDP
     * - Returns policies to PDP based on context
     */
    /**-------------------------------------------------------------------------*/
    final class PRP {

        public function __construct(private PolicyManager $pm){}
        public function findByName(string $name): ?Policy{return $this->pm->findByName($name);}
        public function findByActor(string $actor): array{return $this->pm->findByActor($actor);}
        public function findBySubject(string $subject): array{return $this->pm->findBySubject($subject);}
        public function findAll(){return $this->pm->findAll();}

        /**-------------------------------------------------------------------------*/
        /**
         * 
         */
        /**-------------------------------------------------------------------------*/
        public function findTargetPolicies(string $action, PolicyContext $context){
            // Grab policies from Policy Manager
            $policies = $this->pm->findAll();

            // Declare results
            $results = [];

            // Get class names for the context objects
            $contextActor       = $this->getClassShortName($context->getActor());
            $contextSubjects    = array_map(function($obj){
                return $this->getClassShortName($obj);
            }, $context->getSubjects());

            // Loop policies and check for matches
            foreach($policies as $policy){
                // Skip if action not found
                if(!in_array($action, $policy->getActions())){
                    continue;
                }

                // Skip if actor does not match
                if(!in_array($contextActor, $policy->getActors())){
                    continue;
                }
                

                // Check for required subjects
                if (!empty($policy->getSubjects())) {
                    // Find the intersection of subjects between the policy and the context.
                    $intersection = array_intersect($policy->getSubjects(), $contextSubjects);

                    // If there is no intersection, the context lacks a required subject.
                    if (empty($intersection)) {
                        continue;
                    }
                }

                // Policies that passed test
                // Push policy
                $results[] = $policy;
            }

            // Return found policies
            return $results;
        }

        /**-------------------------------------------------------------------------*/
        /**
         * 
         */
        /**-------------------------------------------------------------------------*/
        private function getClassShortName(object $entity){
            return (new \ReflectionClass($entity))->getShortName();
        }

        /**-------------------------------------------------------------------------*/
    }
?>