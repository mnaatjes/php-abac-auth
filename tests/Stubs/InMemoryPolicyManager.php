<?php

namespace mnaatjes\ABAC\Tests\Stubs;

use mnaatjes\ABAC\Contracts\Policy;
use mnaatjes\ABAC\Contracts\PolicyManager;

class InMemoryPolicyManager implements PolicyManager
{
    /** @var Policy[] */
    private array $policies = [];

    public function __construct(array $policies = [])
    {
        $this->policies = $policies;
    }

    public function findByName(string $name): ?Policy
    {
        foreach ($this->policies as $policy) {
            if ($policy->getName() === $name) {
                return $policy;
            }
        }
        return null;
    }

    public function findByEffect(string $effect): array
    {
        return array_filter($this->policies, fn(Policy $p) => $p->getEffect() === $effect);
    }

    public function findByActor(string $actor): array
    {
        return array_filter($this->policies, fn(Policy $p) => in_array($actor, $p->getActors()));
    }

    public function findBySubject(string $subject): array
    {
        return array_filter($this->policies, fn(Policy $p) => in_array($subject, $p->getSubjects()));
    }

    public function findAll(): array
    {
        return $this->policies;
    }

    public function save(Policy $policy): void
    {
        $this->policies[] = $policy;
    }

    public function delete(string $policyName): bool
    {
        $initialCount = count($this->policies);
        $this->policies = array_filter($this->policies, fn(Policy $p) => $p->getName() !== $policyName);
        return count($this->policies) < $initialCount;
    }
}
