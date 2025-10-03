<?php
    namespace mnaatjes\ABAC\Contracts;
    use mnaatjes\ABAC\Foundation\PIP;

    /**
     * PolicyContext
     * 
     * Represents the context of an access request.
     * Contains information about the subject, resource, action, and environment.
     * 
     * @package mnaatjes\ABAC\Contracts
     * @version 1.0
     * @since 1.0
     * @category Policy
     * 
     */
    final readonly class PolicyContext {
        /**-------------------------------------------------------------------------*/
        /**
         * Constructor
         * 
         * @param PIP $actor
         * @param PIP[] $subjects
         * @param mixed[] $environment
         * 
         */
        /**-------------------------------------------------------------------------*/
        public function __construct(
            /**
             * @param PIP $actor
             */
            private PIP $actor,
            /**
             * @param PIP[] $subjects - Indexed array of Policy Information Points
             */
            private array $subjects=[],
            /**
             * @param mixed[] $environment - Assoc Array of Environmental Properties
             */
            private array $environment=[]
        ) {
            // Validate each subject is a PolicyInformationPoint
            foreach ($this->subjects as $subject) {
                if (!$subject instanceof PIP) {
                    // One of the Subjects is NOT a Policy Information point
                    throw new \TypeError('All subjects must implement PolicyInformationPoint.');
                }
            }
        }

        /**-------------------------------------------------------------------------*/
        /**
         * Get Actor
         * 
         * @return PIP
         */
        /**-------------------------------------------------------------------------*/
        public function getActor(): PIP{return $this->actor;}

        /**-------------------------------------------------------------------------*/
        /**
         * Get Subjects
         * 
         * @return PIP[]
         */
        /**-------------------------------------------------------------------------*/
        public function getSubjects(): array{return $this->subjects;}

        /**-------------------------------------------------------------------------*/
        /**
         * Get Environment
         * 
         * @return mixed[]
         */
        /**-------------------------------------------------------------------------*/
        public function getEnvironments(): array{return $this->environment;}

        /**-------------------------------------------------------------------------*/
        /**
         * Get Subject By Classname
         * 
         * @param string $name
         * @return PIP|null
         */
        /**-------------------------------------------------------------------------*/
        public function getSubjectByClassname(string $name): ?PIP {
            // Loop through subjects
            foreach ($this->getSubjects() as $subject) {
                // Use reflection to get short name and evaluate
                $shortName = (new \ReflectionClass($subject))->getShortName();
                if ($shortName === $name) {
                    return $subject;
                }
            }

            // Return Default
            return NULL;
        }
    }
?>