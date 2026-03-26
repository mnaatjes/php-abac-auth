<?php

namespace mnaatjes\ABAC\Tests\Unit\Foundation;

use mnaatjes\ABAC\Foundation\PRP;
use mnaatjes\ABAC\Contracts\PolicyContext;
use mnaatjes\ABAC\Tests\Stubs\User;
use mnaatjes\ABAC\Tests\Stubs\Post;
use mnaatjes\ABAC\Contracts\Policy;
use mnaatjes\ABAC\Tests\Stubs\InMemoryPolicyManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers \mnaatjes\ABAC\Foundation\PRP
 */
class PRPTest extends TestCase
{
    public function test_find_target_policies_returns_matching_policy(): void
    {
        $matchingPolicy = new Policy('match', 'permit', ['edit'], ['User'], ['Post'], ['condition' => 'AND', 'expressions' => []]);
        $nonMatchingPolicy = new Policy('no-match', 'permit', ['delete'], ['Admin'], [], ['condition' => 'AND', 'expressions' => []]);

        $policyManager = new InMemoryPolicyManager([$matchingPolicy, $nonMatchingPolicy]);
        $prp = new PRP($policyManager);
        $context = new PolicyContext(new User('test'), [new Post(true, 'draft')]);

        $policies = $prp->findTargetPolicies('edit', $context);

        $this->assertCount(1, $policies);
        $this->assertSame($matchingPolicy, $policies[0]);
    }

    public function test_find_target_policies_returns_empty_array_when_action_does_not_match(): void
    {
        $policy = new Policy('p1', 'permit', ['view'], ['User'], [], ['condition' => 'AND', 'expressions' => []]);
        $policyManager = new InMemoryPolicyManager([$policy]);
        $prp = new PRP($policyManager);
        $context = new PolicyContext(new User('test'), []);

        $policies = $prp->findTargetPolicies('edit', $context);

        $this->assertCount(0, $policies);
    }

    public function test_find_target_policies_returns_empty_array_when_actor_does_not_match(): void
    {
        $policy = new Policy('p1', 'permit', ['edit'], ['Admin'], [], ['condition' => 'AND', 'expressions' => []]);
        $policyManager = new InMemoryPolicyManager([$policy]);
        $prp = new PRP($policyManager);
        $context = new PolicyContext(new User('test'), []);

        $policies = $prp->findTargetPolicies('edit', $context);

        $this->assertCount(0, $policies);
    }

    public function test_find_target_policies_requires_all_subjects(): void
    {
        // This policy requires a Post AND an Article
        $policy = new Policy('p1', 'permit', ['edit'], ['User'], ['Post', 'Article'], ['condition' => 'AND', 'expressions' => []]);
        $policyManager = new InMemoryPolicyManager([$policy]);
        $prp = new PRP($policyManager);

        // This context only provides a Post, so it should NOT match.
        $context = new PolicyContext(new User('test'), [new Post(true, 'draft')]);

        $policies = $prp->findTargetPolicies('edit', $context);

        $this->assertCount(0, $policies, 'Should not match when a required subject is missing');
    }
}
