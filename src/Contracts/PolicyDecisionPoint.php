<?php

    namespace mnaatjes\ABAC\Contracts;
    use mnaatjes\ABAC\Contracts\PolicyInformationPoint;

    interface PolicyDecisionPoint {
        public function decide(string $action, PolicyInformationPoint $actor, ?PolicyInformationPoint $subject = null): bool;
    }
?>