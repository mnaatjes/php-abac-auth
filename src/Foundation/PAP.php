<?php

    namespace mnaatjes\ABAC\Foundation;
    use mnaatjes\ABAC\Contracts\Policy;
use mnaatjes\ABAC\Contracts\PolicyManager;
use mnaatjes\ABAC\Foundation\PRP;

    /**
     * PAP: Policy Administration Point
     * The tool or interface used to manage policies.
     * - Create
     * - Update
     * - Delete
     * - Manage
     * - Deploy
     */
    final class PAP {

        public function __construct(private PolicyManager $pm){}
        public function createPolicy(Policy $policy): void {}
        public function updatePolicy(string $policyName, array|Policy $updatedPolicy): void {}
        public function deletePolicy(string $policyName): void {}
        public function listAll(){return $this->pm->findAll();}
    }
?>