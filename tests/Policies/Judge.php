<?php

    namespace mnaatjes\ABAC\Tests\Policies;
    use mnaatjes\ABAC\AuthorizationResponse;
    use mnaatjes\ABAC\Contracts\PolicyContext;
    use mnaatjes\ABAC\Contracts\PolicyDecisionPoint;

    class Judge implements PolicyDecisionPoint {

        public function decide(string $action, PolicyContext $context): AuthorizationResponse{
            // Representing the rules / rulebook
            // Get Rules to evaluate
            $rules = require(__DIR__ . "/../Data/config.php");

            // Capture actor name
            $name = $context->getActorProp("name");

            // Perform Judgement
            foreach($context->getSubjects() as $subject){
                // Ensure all 
                if(!in_array($subject->name, array_keys($rules))){
                    // Return Failure Response
                    return AuthorizationResponse::deny("The actor '$name' is NOT permitted!");
                }
            }

            // Loop Completed
            // Success
            return AuthorizationResponse::allow();
        }
    }
?>