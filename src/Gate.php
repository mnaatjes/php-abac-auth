<?php

    namespace mnaatjes\ABAC;

    use mnaatjes\ABAC\Contracts\PolicyDecisionPoint;
    use mnaatjes\ABAC\Contracts\PolicyContext;

    /**
     * Gate
     */
    final class Gate {
        public function __construct(private PolicyDecisionPoint $pdp){}
        public function authorize(string $action, PolicyContext $context){
            // Capture response from decision
            $AuthResponse = $this->pdp->decide($action, $context);

            // Evaluate Auth Response Object
            if($AuthResponse->allowed === false){
                throw new \Exception($AuthResponse->message ?? "Action `{$action}` is NOT Allowed!");
            }
        }

    }
?>