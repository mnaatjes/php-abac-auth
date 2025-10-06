<?php

namespace mnaatjes\ABAC\Tests\Unit\Foundation;

use mnaatjes\ABAC\Contracts\PolicyContext;
use mnaatjes\ABAC\Foundation\PDP;
use mnaatjes\ABAC\Foundation\PRP;
use mnaatjes\ABAC\Tests\Stubs\User;
use mnaatjes\ABAC\Tests\Stubs\Post;
use mnaatjes\ABAC\Contracts\Policy;
use mnaatjes\ABAC\Tests\Stubs\InMemoryPolicyManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers \mnaatjes\ABAC\Foundation\PDP
 */
class PDPTest extends TestCase
{
    private $context;

    protected function setUp(): void
    {
        $this->context = new PolicyContext(new User('test'), [new Post(true, 'draft')]);
    }

    public function test_decide_returns_deny_when_no_policies_are_found(): void
    {
        $policyManager = new InMemoryPolicyManager([]);
        $prp = new PRP($policyManager);
        $pdp = new PDP($prp);

        $decision = $pdp->decide('edit', $this->context);

        $this->assertFalse($decision->allowed);
        $this->assertEquals('No policy explicitly permitted this action', $decision->message);
    }

    public function test_decide_returns_permit_when_a_matching_permit_policy_is_found(): void
    {
        $permitPolicy = new Policy('p1', 'permit', ['edit'], ['User'], ['Post'], ['condition' => 'AND', 'expressions' => []]);
        $policyManager = new InMemoryPolicyManager([$permitPolicy]);
        $prp = new PRP($policyManager);
        $pdp = new PDP($prp);

        $decision = $pdp->decide('edit', $this->context);

        $this->assertTrue($decision->allowed);
    }

    public function test_decide_returns_deny_when_a_matching_deny_policy_is_found(): void
    {
        $denyPolicy = new Policy('DenyPolicy', 'deny', ['edit'], ['User'], ['Post'], ['condition' => 'AND', 'expressions' => []]);
        $policyManager = new InMemoryPolicyManager([$denyPolicy]);
        $prp = new PRP($policyManager);
        $pdp = new PDP($prp);

        $decision = $pdp->decide('edit', $this->context);

        $this->assertFalse($decision->allowed);
        $this->assertEquals('Access Denied by DenyPolicy', $decision->message);
    }

    public function test_decide_prioritizes_deny_over_permit_when_both_match(): void
    {
        $permitPolicy = new Policy('PermitPolicy', 'permit', ['edit'], ['User'], ['Post'], ['condition' => 'AND', 'expressions' => []]);
        $denyPolicy = new Policy('DenyPolicy', 'deny', ['edit'], ['User'], ['Post'], ['condition' => 'AND', 'expressions' => []]);
        $policyManager = new InMemoryPolicyManager([$denyPolicy, $permitPolicy]);
        $prp = new PRP($policyManager);
        $pdp = new PDP($prp);

        $decision = $pdp->decide('edit', $this->context);

        $this->assertFalse($decision->allowed);
        $this->assertEquals('Access Denied by DenyPolicy', $decision->message);
    }
}