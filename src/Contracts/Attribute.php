<?php

    namespace mnaatjes\ABAC\Contracts;
    use mnaatjes\ABAC\Contracts\PolicyContext;
    use mnaatjes\ABAC\Support\AttributeAccessor;

    final class Attribute {
        public function __construct(
            /**
             * @var string $entity - Actor, Subject, Environment, or Literal
             */
            private ?string $entity,
            /**
             * @var string $name - Name of the attribute property: e..g "id"
             */
            private ?string $name=NULL,
            /**
             * @var mixed $literal - Explicitly declared literal value
             */
            private mixed $literal=NULL
        ){}
        public function getValue(PolicyContext $context, AttributeAccessor $accessor): mixed{return NULL;}
    }
?>