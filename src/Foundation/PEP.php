<?php

    namespace mnaatjes\ABAC\Foundation;
    use mnaatjes\ABAC\Foundation\PDP;
    use mnaatjes\ABAC\Contracts\PolicyContext;
    use mnaatjes\ABAC\Contracts\Decision;

    /**
     * PEP: Policy Enforcement Point
     * The component that enforces access decisions.
     * - Intercepts access requests
     * - Calls PDP to get decision
     * - Enforces decision (allow or deny access)
     */
    final class PEP {
        private PDP $pdp;

        public function __construct(PDP $pdp) {
            $this->pdp = $pdp;
        }

        public function enforce(string $action, PolicyContext $context): void {
            // Resolve Decision
            $descision = $this->pdp->decide($action, $context);

            // Evaluate Decision
            if($descision->allowed === false){
                //throw new \Exception($descision->message ?? "Action `{$action}` is NOT allowed!");
                var_dump("Decision: False");
            }
        }
    }
?>