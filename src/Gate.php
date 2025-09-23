<?php

    namespace mnaatjes\ABAC;

    use mnaatjes\ABAC\Contracts\PolicyDecisionPoint;
    use mnaatjes\ABAC\Contracts\PolicyContext;
    use mnaatjes\ABAC\AuthorizationException;

    final class Gate {
        public function __construct(private PolicyDecisionPoint $pdp){}
        public function authorize(string $action, PolicyContext $context){
            // Run PDP decide method
            if($this->pdp->decide($action, $context) === false){
                throw new AuthorizationException("Action `{$action}` is NOT Allowed!");
            }
        }

    }
?>