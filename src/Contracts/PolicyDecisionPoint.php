<?php

    namespace mnaatjes\ABAC\Contracts;
    use mnaatjes\ABAC\AuthorizationResponse;

    interface PolicyDecisionPoint {
        public function decide(string $action, PolicyContext $context): AuthorizationResponse;
    }
?>