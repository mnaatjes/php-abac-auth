<?php

    namespace mnaatjes\ABAC\Tests\Policies;
    use mnaatjes\ABAC\Contracts\PolicyContext;
    use mnaatjes\ABAC\Contracts\PolicyDecisionPoint;

    class Judge implements PolicyDecisionPoint {

        public function decide(string $action, PolicyContext $context): bool{
            // Evaluate and return
            return in_array($context->getActorProp("name"), $context->getSubjectKeys());
        }
    }
?>