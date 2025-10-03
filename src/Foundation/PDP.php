<?php

    namespace mnaatjes\ABAC\Foundation;
    use mnaatjes\ABAC\Contracts\Decision;
    use mnaatjes\ABAC\Contracts\PolicyContext;
    use mnaatjes\ABAC\Foundation\PRP;
    use mnaatjes\ABAC\Contracts\Policy;
    use mnaatjes\ABAC\Support\AttributeAccessor;

    /**-------------------------------------------------------------------------*/
    /**
     * PDP: Policy Decision Point
     * The component that evaluates access requests against policies.
     * - Receives access requests
     * - Queries PRP for relevant policies
     * - Evaluates policies against request context
     * - Returns permit or deny decision
     */
    /**-------------------------------------------------------------------------*/
    final class PDP {

        /**
         * @var AttributeAccessor $accessor
         */
        private AttributeAccessor $accessor;
        
        /**-------------------------------------------------------------------------*/
        /**
         * 
         */
        /**-------------------------------------------------------------------------*/
        public function __construct(protected PRP $prp){
            // Create new Attribute Accessor Instance
            $this->accessor = new AttributeAccessor();
        }

        /**-------------------------------------------------------------------------*/
        /**
         * 
         */
        /**-------------------------------------------------------------------------*/
        public function decide(string $action, PolicyContext $context): Decision{
            // Find policies that match context criteria
            $policies = $this->prp->findTargetPolicies($action, $context);

            /**
             * @var array $denyPolicies - List of policies from $policies with "effect: deny"
             */
            $denyPolicies = [];

            /**
             * @var array $permitPolicies - List of policies from $policies with "effect: permit"
             */
            $permitPolicies = [];

            // Partition policies by effect
            foreach($policies as $policy){
                if($policy->getEffect() === 'deny'){
                    $denyPolicies[] = $policy;
                } else if($policy->getEffect() === 'permit'){
                    $permitPolicies[] = $policy;
                }
            }

            // Evaluate Deny Policies first
            foreach($denyPolicies as $policy){
                // Evaluate
                if($this->evaluatePolicyRules($policy, $context)){
                    return Decision::deny("Access Denied by " . $policy->getName());
                }
            }

            // Evaluate Permit Policies
            foreach($permitPolicies as $policy){
                // Evaluate
                if($this->evaluatePolicyRules($policy, $context)){
                    return Decision::permit();
                }
            }
                
            // Return Default: DENY
            return Decision::deny("No policy explicitly permitted this action");
        }

        /**-------------------------------------------------------------------------*/
        /**
         * 
         */
        /**-------------------------------------------------------------------------*/
        private function evaluatePolicyRules(Policy $policy, PolicyContext $context){
            // Get rules from policy
            $rules = $policy->getRules();

            // No expressions exist
            // Defaults to true / a match
            if(empty($rules->getExpressions())){
                return true;
            }

            // Declare expression evaluation results
            $results = [];

            // Loop expressions
            foreach($policy->getExpressions() as $expression){
                $results[] = $expression->evaluate($context, $this->accessor);
            }

            // Evaluate Conditions "AND/OR" of Expression Results
            // AND: All Conditions MUST be TRUE
            if(strtoupper($rules->getCondition()) === "AND"){
                // Check if "false" is NOT in array
                return !in_array(false, $results, true);
            }
            // OR: At least ONE condition must be TRUE
            else {
                return in_array(true, $results, true);
            }

        }
    }
?>