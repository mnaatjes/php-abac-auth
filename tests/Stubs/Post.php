<?php

    namespace mnaatjes\ABAC\Tests\Stubs;
    use mnaatjes\ABAC\Foundation\PIP;

    class Post implements PIP {
        public function __construct(
            private bool $premium
        ){}

        public function isPremium(){return $this->premium;}
    }

?>