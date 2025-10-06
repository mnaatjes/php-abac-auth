<?php

namespace mnaatjes\ABAC\Tests\Unit;

use mnaatjes\ABAC\Contracts\PolicyContext;
use mnaatjes\ABAC\Tests\Stubs\Post;
use mnaatjes\ABAC\Tests\Stubs\User;
use mnaatjes\ABAC\Tests\Stubs\InMemoryABACFactory;
use mnaatjes\ABAC\Contracts\Policy;
use mnaatjes\ABAC\Contracts\Rules;
use mnaatjes\ABAC\Contracts\Expressions\ExpressionInterface;
use mnaatjes\ABAC\Support\ExpressionFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \mnaatjes\ABAC\ABAC
 */
class ABACTest extends TestCase
{
    /**
     * @covers \mnaatjes\ABAC\ABAC
     */
    public function test_runtime_permits_an_allowed_action(): void
    {
        // 1. ARRANGE
        $policy = new Policy(
            'allow-admins-to-edit-posts',
            'permit',
            ['edit'],
            ['User'],
            ['Post'],
            [
                'condition' => 'AND',
                'expressions' => [
                    ['actor_attribute' => 'role', 'operator' => '==', 'value' => 'Admin']
                ]
            ]
        );

        // Create a context where the user IS an Admin
        $context = new PolicyContext(
            actor: new User('Admin'),
            subjects: [new Post(true, 'draft')]
        );

        // 2. ACT
        // Create the entire runtime via the in-memory factory
        $pep = InMemoryABACFactory::createRuntime([$policy]);

        // Enforce the policy
        $pep->enforce('edit', $context);

        // 3. ASSERT
        // If enforce() did not throw, the test passed.
        $this->assertTrue(true, 'No exception was thrown for an allowed action.');
    }

    public function test_runtime_throws_exception_for_a_denied_action(): void
    {
        // 1. ARRANGE
        $policy = new Policy(
            'allow-admins-to-edit-posts',
            'permit',
            ['edit'],
            ['User'],
            ['Post'],
            [
                'condition' => 'AND',
                'expressions' => [
                    ['actor_attribute' => 'role', 'operator' => '==', 'value' => 'Admin']
                ]
            ]
        );

        // Create a context where the user IS NOT an Admin
        $context = new PolicyContext(
            actor: new User('Guest'),
            subjects: [new Post(true, 'draft')]
        );

        // 2. ASSERT: Tell PHPUnit to expect an exception.
        $this->expectException(\Exception::class);

        // 3. ACT
        $pep = InMemoryABACFactory::createRuntime([$policy]);

        // This line should now fail and throw the exception.
        $pep->enforce('edit', $context);
    }
}
