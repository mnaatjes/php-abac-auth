<?php

    namespace mnaatjes\ABAC\Foundation;
    use mnaatjes\ABAC\Contracts\Decision;
    use mnaatjes\ABAC\Contracts\PolicyContext;
    use mnaatjes\ABAC\Foundation\PRP;
    
    /**
     * PDP: Policy Decision Point
     * The component that evaluates access requests against policies.
     * - Receives access requests
     * - Queries PRP for relevant policies
     * - Evaluates policies against request context
     * - Returns permit or deny decision
     */
    final class PDP {
        
        public function __construct(protected PRP $prp){}
        public function decide(string $action, PolicyContext $context): Decision{
            // TODO: Complete Evaluation Logic

            // Evaluate DENY Policies First

            // Evaluate PERMIT Policies
                // Allow
                
            // Return Default: DENY
            return new Decision(false, "Unable to resolve decision!");
        }

        private function doesPolicyApply(){}
        private function evaluateExpression(){}
    }
?>