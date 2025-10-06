<?php

namespace mnaatjes\ABAC\Tests\Stubs;

use mnaatjes\ABAC\Foundation\PIP;

class Post implements PIP
{
    public function __construct(
        private bool $premium,
        private string $status
    ){}

    public function isPremium(): bool
    {
        return $this->premium;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
