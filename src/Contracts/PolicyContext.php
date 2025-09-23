<?php

    namespace mnaatjes\ABAC\Contracts;
    use mnaatjes\ABAC\Contracts\PolicyInformationPoint;

    final class PolicyContext implements PolicyInformationPoint {

        /**
         * Create a new PolicyContext
         * 
         * @param PolicyInformationPoint $actor The primary actor performing the action.
         * - actor: The primary user or system performing the action
         * @param array<PolicyInformationPoint> $subjects An array of subject information. 
         * - subjects: An array of resources being acted upon (e.g., a Post, a Comment).
         * @param array<string,mixed> $environment Any other relevant environmental context (e.g., IP address, time).
         * - environment: A flexible array for any other contextual attributes that aren't part of the actor or subjects.
         * - Where is the request from? (e.g., 'ip_address' => '192.168.1.1')
         * - When is it happening? (e.g., 'current_time' => new \DateTimeImmutable())
         * - How is the user authenticated? (e.g., 'mfa_enabled' => true)
         */
        public function __construct(
            public readonly PolicyInformationPoint $actor,
            public readonly array $subjects = [],
            public readonly array $environment = []
        ){
            // Validate each subject is a PolicyInformationPoint
            foreach ($this->subjects as $subject) {
                if (!$subject instanceof PolicyInformationPoint) {
                    throw new \InvalidArgumentException('All subjects must implement PolicyInformationPoint.');
                }
            }
        }

        public function getActor(): PolicyInformationPoint{return $this->actor;}
        public function getActorProp(string $prop){return $this->actor->$prop;}
        public function getSubjects(): array{return $this->subjects;}
        public function getSubjectKeys(): array{return array_keys($this->subjects);}
        public function getSubjectValues(): array{return array_values($this->subjects);}
    }

?>