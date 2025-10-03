<?php

    namespace mnaatjes\ABAC\Tests\Stubs;
    use mnaatjes\ABAC\Foundation\PIP;

    /**
     * Item
     * 
     */
    class Item implements PIP {
        public function __construct(
            private bool $premium
        ){}

        public function isPremium(){return $this->premium;}
    }
?>