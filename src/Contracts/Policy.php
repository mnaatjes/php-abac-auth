<?php

    namespace mnaatjes\ABAC\Contracts;

    /**
     * Policy
     * Represents an access control policy.
     * Contains rules that define access permissions based on attributes.
     */
    final readonly class Policy {

        private array $expressions;

        public function __construct(
            private string $name,
            private string $effect, // "permit" or "deny"
            private array $actors,
            private array $actions,
            private array $subjects,
            private array $rules,
            private ?string $description=NULL
        ) {
            // Determine Properties of each Expression
            $acc = [];
            foreach($this->rules["expressions"] as $expression){
                $acc[] = $expression;
            }
            $this->expressions = $acc;
            // Map each expression element as an object
        }

        public function getName(): string{return $this->name;}
        public function getEffect(): string{return $this->effect;}
        public function getRules(): array{return $this->rules;}
        public function getDescription(): string{return $this->description;}
        public function getActors(): array{return $this->subjects;}
        public function getSubjects(): array{return $this->subjects;}
        public function getExpressions(): array{return $this->expressions;}
        

        /**-------------------------------------------------------------------------*/
        /**
         * Checks if a policy contains a specific actor
         * 
         * @return bool
         */
        /**-------------------------------------------------------------------------*/
        public function hasActor(string $actor): bool{
            return in_array($actor, $this->getActors());
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Checks if a policy contains a specific subject
         * 
         * @return bool
         */
        /**-------------------------------------------------------------------------*/
        public function hasSubject(string $subject): bool{
            return in_array($subject, $this->getSubjects());
        }
    }
?>