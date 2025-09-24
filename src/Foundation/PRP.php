<?php

    namespace mnaatjes\ABAC\Foundation;
    use mnaatjes\ABAC\Contracts\Policy;
    use mnaatjes\ABAC\Contracts\PolicyManager;

    /**
     * PRP: Policy Retrieval Point
     * The component that queries the YAML or JSON file (store) to retrieve policies.
     * - Called by the PDP
     * - Returns policies to PDP based on context
     */
    final class PRP {

        public function __construct(private PolicyManager $pm){}
        public function findByName(string $name): ?Policy{return $this->pm->findByName($name);}
        public function findByActor(string $actor): array{return $this->pm->findByActor($actor);}
        public function findBySubject(string $subject): array{return $this->pm->findBySubject($subject);}
    }
?>