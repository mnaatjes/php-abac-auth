<?php

    namespace mnaatjes\ABAC\Contracts;
    use mnaatjes\ABAC\Contracts\Rules\Rule;
    /**
     * Policy
     * Represents an access control policy.
     * Contains rules that define access permissions based on attributes.
     */
    final readonly class Policy {

        private Rule $rules;

        /**-------------------------------------------------------------------------*/
        /**
         * 
         */
        /**-------------------------------------------------------------------------*/
        public function __construct(
            private string $name,
            private string $effect, // "permit" or "deny"
            private array $actors,
            private array $actions,
            private array $subjects,
            private array $rules_array,
            private ?string $description=NULL
        ) {
            // Assign Rule and Expressions Objects
            $this->rules = new Rule(
                $rules_array["condition"],
                $rules_array["expressions"]
            );
        }

        public function getName(): string{return $this->name;}
        public function getEffect(): string{return $this->effect;}
        public function getRules(): Rule{return $this->rules;}
        public function getDescription(): string{return $this->description;}
        public function getActors(): array{return $this->subjects;}
        public function getActions(): array{return $this->actions;}
        public function getSubjects(): array{return $this->subjects;}
        public function getExpressions(): array{return $this->rules->getExpressions();}
        

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