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
            // Find Policies with aligned Action
            $policiesWithAction = $this->findPolicyForAction($action);

            // Extract Contextual Information
            // Get Actor string
            $actorName = $this->getClassShortName($context->getActor()) ?? NULL;

            // Get Subject string
            $subjectNames = array_map(function($pip){
                return $this->getClassShortName($pip) ?? NULL;
            }, $context->getSubjects());

            // Filter by Actor
            $policiesWithActor = $this->filterByActor($actorName, $policiesWithAction) ?? NULL;

            // Filter by Subjects
            $policiesWithSubjects = $this->filterBySubjects($subjectNames, $policiesWithActor);

            // TODO: Check Environment

            // Return array of refined policies
            return $policiesWithSubjects;

        }

        /**-------------------------------------------------------------------------*/
        /**
         * 
         */
        /**-------------------------------------------------------------------------*/
        private function filterBySubjects(array $subjectNames, array $policies){
            // Filter by Subjects
            $results = [];

            // Cycle Through Policies
            foreach($policies as $policy){
                // Cycle through Subjects
                $results = array_merge(
                    $results, array_reduce(
                        $subjectNames, function($acc, $name) use($policy){
                            // Find Subject
                            if($policy->hasSubject($name)){
                                $acc[] = $policy;
                            }

                            // Return
                            return $acc;
                        }, []
                    )
                );
            }

            // Return array
            return $results;
        }

        /**-------------------------------------------------------------------------*/
        /**
         * 
         */
        /**-------------------------------------------------------------------------*/
        private function findPolicyForAction(string $action){
            return array_filter($this->findAll(), function($policy) use($action){
                return in_array($action, $policy->getActions());
            });
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
        /**
         * 
         */
        /**-------------------------------------------------------------------------*/
        private function filterByActor(string|NULL $actor_name, array $policies){
            // Check if actor NULL
            if(is_null($actor_name)){
                return NULL;
            }

            // Reduce Array of Policies
            return array_reduce($policies, function($acc, $policy) use($actor_name){
                // Parse
                if($policy->hasActor($actor_name)){
                    $acc[] = $policy;
                }

                // Return acc
                return $acc;
            }, []);
        }
    }
?>