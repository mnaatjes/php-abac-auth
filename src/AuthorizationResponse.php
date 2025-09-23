<?php

    namespace mnaatjes\ABAC;

    /**
     * 
     */
    final readonly class AuthorizationResponse {
        public function __construct(
            private readonly bool $allowed,
            private readonly ?string $reason=NULL
        ){}
    }
?>