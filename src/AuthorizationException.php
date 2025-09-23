<?php

    namespace mnaatjes\ABAC;

    class AuthorizationException extends \Exception {
        public function __construct(
            string $message="Action is not authorized",
            int $code=403,
            ?\Throwable $previous=NULL
        ){
            // pass to parent
            parent::__construct($message, $code, $previous);            
        }
    }
?>