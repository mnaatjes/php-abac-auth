<?php

    namespace mnaatjes\ABAC\Tests\DataObjects;

    use mnaatjes\ABAC\Contracts\PolicyInformationPoint;

    final readonly class Category implements PolicyInformationPoint {
        public function __construct(
            public readonly string $name,
            public readonly string $description
        ){}
    }
?>