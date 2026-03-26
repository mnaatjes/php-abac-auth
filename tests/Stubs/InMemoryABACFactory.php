<?php

namespace mnaatjes\ABAC\Tests\Stubs;

use mnaatjes\ABAC\Foundation\PEP;
use mnaatjes\ABAC\Foundation\PDP;
use mnaatjes\ABAC\Foundation\PRP;
use mnaatjes\ABAC\Contracts\PolicyManager;

/**
 * A test-only factory to create a PEP with an in-memory policy manager.
 * This avoids hitting the static ABAC factory and the filesystem.
 */
class InMemoryABACFactory
{
    public static function createRuntime(array $policies = []): PEP
    {
        $policyManager = new InMemoryPolicyManager($policies);
        $prp = new PRP($policyManager);
        $pdp = new PDP($prp);
        return new PEP($pdp);
    }
}
