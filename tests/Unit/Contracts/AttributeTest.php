<?php

    namespace mnaatjes\ABAC\Tests\Unit\Contracts;
    use mnaatjes\ABAC\Contracts\Attribute;
    use PHPUnit\Framework\TestCase;
    use mnaatjes\ABAC\Contracts\PolicyContext;
    use mnaatjes\ABAC\Support\AttributeAccessor;
    use mnaatjes\ABAC\Tests\Stubs\User;

    class AttributeTest extends TestCase
    {
        /**
         * @covers \mnaatjes\ABAC\Contracts\Attribute
         * @covers \mnaatjes\ABAC\Contracts\Attribute::getValue
         * @covers \mnaatjes\ABAC\Support\AttributeAccessor
         * 
         * @return void
         */
        public function test_get_value_returns_literal_for_literal_entity(): void{
            $attr = new Attribute(
                entity: "literal",
                name: NULL,
                literal: "my-test-value"
            );

            $context = new PolicyContext(
                actor: new User('dummy-user'),
                subjects: [],
                environment: []
            );

            $accessor = new AttributeAccessor();

            $this->assertEquals("my-test-value", $attr->getValue($context, $accessor, NULL));
        }
    }
?>