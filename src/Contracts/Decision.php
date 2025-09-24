<?php
    namespace mnaatjes\ABAC\Contracts;
    
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
?>