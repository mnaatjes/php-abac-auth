<?php

    namespace mnaatjes\ABAC\Contracts;
    use mnaatjes\ABAC\Contracts\PolicyInformationPoint;

    interface PolicyDecisionPoint {
        public function decide(string $action, PolicyContext $context): bool;
    }
?>