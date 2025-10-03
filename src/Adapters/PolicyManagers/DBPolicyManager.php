<?php

    namespace mnaatjes\ABAC\Adapters\PolicyManagers;
    use mnaatjes\ABAC\Contracts\PolicyManager;
    use mnaatjes\ABAC\Contracts\Policy;

    /**
     * DBPolicyManager
     * 
     * @package mnaatjes\ABAC
     * @version 1.0
     * @since 1.0
     * @category PolicyManagers
     * 
     */
    final class DBPolicyManager implements PolicyManager {
        public function findByName(string $name): ?Policy{return NULL;}
        public function findByEffect(string $effect): array{return [];}
        public function findByActor(string $actor): array{ return [];}
        public function findBySubject(string $subject): array{return [];}
        public function findAll(): array{return [];}
        public function save(policy $policy): void{}
        public function delete(string $policyName): bool{return false;}
    }
?>