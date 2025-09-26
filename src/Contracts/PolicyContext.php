<?php
    namespace mnaatjes\ABAC\Contracts;
    use mnaatjes\ABAC\Foundation\PIP;
    /**
     * PolicyContext
     * Represents the context of an access request.
     * Contains information about the subject, resource, action, and environment.
     */
    final readonly class PolicyContext {
        public function __construct(
            /**
             * @param PIP $actor
             */
            public PIP $actor,
            /**
             * @param PIP[] $subjects - Indexed array of Policy Information Points
             */
            public array $subjects=[],
            /**
             * @param mixed[] $environment - Assoc Array of Environmental Properties
             */
            public array $environment=[]
        ) {
            // Validate each subject is a PolicyInformationPoint
            foreach ($this->subjects as $subject) {
                if (!$subject instanceof PIP) {
                    // One of the Subjects is NOT a Policy Information point
                    throw new \TypeError('All subjects must implement PolicyInformationPoint.');
                }
            }
        }
    }
?>