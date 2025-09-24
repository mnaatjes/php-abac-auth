<?php

    /**
     * @file abac-auth/tests/oop_main.php
     * @version 0.1.0
     * @package abac-auth
     * @license MIT
     */

    // require autoloader
    require_once __DIR__ . '/../vendor/autoload.php';

    /**
     * Policy
     * Represents an access control policy.
     * Contains rules that define access permissions based on attributes.
     */
    final readonly class Policy {
        public function __construct(
            public string $name,
            public string $effect, // "permit" or "deny"
            public array $rules,
            public ?string $description=NULL
        ) {}
    }
    /**
     * PRP: Policy Retrieval Point
     * The component that queries the YAML or JSON file (store) to retrieve policies.
     * - Called by the PDP
     * - Returns policies to PDP based on context
     */
    abstract class PRP {
        protected $registry;
        protected abstract function boot(): void;
        public abstract function findBySubject(string $subject): array;
        public abstract function findByAction(string $action): array;
        public abstract function findByActor(string $actor): array;
        public abstract function findAll(): array;
    }

    /**
     * PolicyContext
     * Represents the context of an access request.
     * Contains information about the subject, resource, action, and environment.
     */
    final readonly class PolicyContext {
        public function __construct(
            public string $actor,
            public array $subjects=[],
            public array $environment=[]
        ) {}
    }

    /**
     * Decision
     * Represents the outcome of a policy evaluation.
     * Contains whether access is allowed or denied, along with optional message and code.
     */
    final readonly class Decision {
        public function __construct(
            public bool $allowed,
            public ?string $message=NULL,
            public ?int $code=NULL
        ){}

        public static function allow(): self{return new self(true);}
        public static function deny(?string $message=NULL, ?int $code=NULL): self{return new self(false, $message, $code);}
    }

    /**
     * PDP: Policy Decision Point
     * The component that evaluates access requests against policies.
     * - Receives access requests
     * - Queries PRP for relevant policies
     * - Evaluates policies against request context
     * - Returns permit or deny decision
     */
    abstract class PDP {
        protected PRP $prp;
        public abstract function decide(string $action, PolicyContext $context): Decision;
    }

    /**
     * PEP: Policy Enforcement Point
     * The component that enforces access decisions.
     * - Intercepts access requests
     * - Calls PDP to get decision
     * - Enforces decision (allow or deny access)
     */
    final class PEP {
        private PDP $pdp;

        public function __construct(PDP $pdp) {
            $this->pdp = $pdp;
        }

        public function enforce(string $action, PolicyContext $context): Decision {
            return $this->pdp->decide($action, $context);
        }
    }

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

        private PRP $prp;

        public function __construct(PRP $prp) {
            $this->prp = $prp;
        }

        public function createPolicy(Policy $policy): void {}
        public function updatePolicy(string $policyName, array|Policy $updatedPolicy): void {}
        public function deletePolicy(string $policyName): void {}
        public function listAll(){return $this->prp->findAll();}
    }

?>