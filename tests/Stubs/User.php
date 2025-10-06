<?php

namespace mnaatjes\ABAC\Tests\Stubs;

use mnaatjes\ABAC\Foundation\PIP;

class User implements PIP
{
    public function __construct(
        private string $role
    ){}

    public function getRole(){
        return $this->role;
    }
}
