<?php

    namespace mnaatjes\ABAC\Tests\Adapters;
    use mnaatjes\ABAC\Foundation\PIP;

    class Item implements PIP {
        public function __construct(
            private bool $premium
        ){}

        public function isPremium(){return $this->premium;}
    }
?>