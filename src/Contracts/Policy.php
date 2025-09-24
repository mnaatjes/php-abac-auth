<?php

    namespace mnaatjes\ABAC\Contracts;

    /**
     * Policy
     * Represents an access control policy.
     * Contains rules that define access permissions based on attributes.
     */
    final readonly class Policy {
        public function __construct(
            public string $name,
            public string $effect, // "permit" or "deny"
            public array $actions,
            public array $subjects,
            public array $rules,
            public ?string $description=NULL
        ) {}

        public function getName(): string{return $this->name;}
        public function getEffect(): string{return $this->effect;}
        public function getRules(): array{return $this->rules;}
        public function getDescription(): string{return $this->description;}
        public function getSubjects(): array{return $this->subjects;}
        
        /**-------------------------------------------------------------------------*/
        /**
         * Find Actors - if actor_attribute assigned - in expressions
         * 
         * @return array
         */
        /**-------------------------------------------------------------------------*/
        public function getActors(): array{
            return array_reduce($this->rules["expressions"], function($acc, $exp){
                // Perform check
                if(array_key_exists("actor_attribute", $exp)){
                    $acc[] = $exp["actor_attribute"];
                }
                // Return default
                return $acc;
            }, []);
        }

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