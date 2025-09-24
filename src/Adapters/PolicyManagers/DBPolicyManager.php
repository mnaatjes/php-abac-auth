<?php

    namespace mnaatjes\ABAC\Adapters\PolicyManagers;
    use mnaatjes\ABAC\Contracts\PolicyManager;
    use mnaatjes\ABAC\Contracts\Policy;

    class DBPolicyManager implements PolicyManager {
        public function findByName(string $name): ?Policy{return new Policy("", "", [], [], [], "");}
        public function findByEffect(string $effect): array{return [];}
        public function findByActor(string $actor): array{ return [];}
        public function findBySubject(string $subject): array{return [];}
        public function findAll(): array{return [];}
        public function save(policy $policy): void{}
        public function delete(string $policyName): bool{return false;}
    }
?>