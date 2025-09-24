<?php

    namespace mnaatjes\ABAC\Contracts;
    use mnaatjes\ABAC\Contracts\Policy;

    interface PolicyManager {
        public function findByName(string $name): ?Policy;
        public function findByEffect(string $effect): array;
        public function findByActor(string $actor): array;
        public function findBySubject(string $subject): array;
        public function findAll(): array;
        public function save(policy $policy): void;
        public function delete(string $policyName): bool;
    }
?>