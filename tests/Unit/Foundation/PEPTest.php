<?php

namespace mnaatjes\ABAC\Tests\Unit\Foundation;

use mnaatjes\ABAC\Contracts\PolicyContext;
use mnaatjes\ABAC\Foundation\PDP;
use mnaatjes\ABAC\Foundation\PEP;
use mnaatjes\ABAC\Foundation\PRP;
use mnaatjes\ABAC\Tests\Stubs\InMemoryPolicyManager;
use mnaatjes\ABAC\Tests\Stubs\User;
use mnaatjes\ABAC\Tests\Stubs\Post;
use mnaatjes\ABAC\Contracts\Policy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \mnaatjes\ABAC\Foundation\PEP
 */
class PEPTest extends TestCase
{
    public function test_enforce_allows_access_for_permit_decision(): void
    {
        // 1. ARRANGE
        $permitPolicy = new Policy('p1', 'permit', ['edit'], ['User'], ['Post'], ['condition' => 'AND', 'expressions' => []]);
        $policyManager = new InMemoryPolicyManager([$permitPolicy]);
        $prp = new PRP($policyManager);
        $pdp = new PDP($prp);
        $pep = new PEP($pdp);

        $context = new PolicyContext(new User('test'), [new Post(true, 'draft')]);

        // 2. ACT & 3. ASSERT
        // No exception should be thrown.
        $pep->enforce('edit', $context);
        $this->assertTrue(true, 'No exception was thrown for a permit decision.');
    }

    public function test_enforce_throws_exception_for_deny_decision(): void
    {
        // 1. ARRANGE
        $denyPolicy = new Policy('DenyPolicy', 'deny', ['edit'], ['User'], ['Post'], ['condition' => 'AND', 'expressions' => []]);
        $policyManager = new InMemoryPolicyManager([$denyPolicy]);
        $prp = new PRP($policyManager);
        $pdp = new PDP($prp);
        $pep = new PEP($pdp);

        $context = new PolicyContext(new User('test'), [new Post(true, 'draft')]);

        // 2. ASSERT
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Access Denied by DenyPolicy');

        // 3. ACT
        $pep->enforce('edit', $context);
    }

    public function test_enforce_throws_exception_when_no_policy_permits(): void
    {
        // 1. ARRANGE
        // Policy for a different action
        $otherPolicy = new Policy('p1', 'permit', ['view'], ['User'], ['Post'], ['condition' => 'AND', 'expressions' => []]);
        $policyManager = new InMemoryPolicyManager([$otherPolicy]);
        $prp = new PRP($policyManager);
        $pdp = new PDP($prp);
        $pep = new PEP($pdp);

        $context = new PolicyContext(new User('test'), [new Post(true, 'draft')]);

        // 2. ASSERT
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No policy explicitly permitted this action');

        // 3. ACT
        $pep->enforce('edit', $context);
    }
}