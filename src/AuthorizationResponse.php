<?php

    namespace mnaatjes\ABAC;

    /**
     * 
     */
    final readonly class AuthorizationResponse {
        public function __construct(
            public readonly bool $allowed,
            public readonly ?string $message=NULL,
            public readonly ?int $code=NULL
        ){}

        public static function allow(): self{return new self(true);}
        public static function deny(?string $message=NULL, ?int $code=NULL): self{return new self(false, $message, $code);}
    }
?>