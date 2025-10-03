<?php

    namespace mnaatjes\ABAC\Tests\Unit\Contracts\Expressions;
    use mnaatjes\ABAC\Contracts\PolicyContext;
    use mnaatjes\ABAC\Tests\Stubs\User;
    use PHPUnit\Framework\TestCase;
    use mnaatjes\ABAC\Support\AttributeAccessor;
    use mnaatjes\ABAC\Contracts\Attribute;
    use mnaatjes\ABAC\Contracts\Expressions\UnaryExpression;

    class UnaryExpressionTest extends TestCase {
        /**
         * Test that the ! operator inverts a true value
         *  - !true = false
         * 
         * @covers \mnaatjes\ABAC\Contracts\Expressions\UnaryExpression
         * @covers \mnaatjes\ABAC\Contracts\Expressions\UnaryExpression::evaluate
         * 
         * @return void
         * 
         * @test
         */
        public function test_not_operator_inverts_true_value(): void {
            // Arrange
            // Define Context
            $context = new PolicyContext(
                actor: new User('test-user'),
                subjects: [],
                environment: []
            );

            // Define Accessor
            $accessor = new AttributeAccessor();

            // Define Literal Attribute
            $literalTrueAttribute = new Attribute(
                entity: 'literal',
                literal: true
            );

            // Define a Unary Expression
            $expression = new UnaryExpression(
                operator: '!',
                operand: $literalTrueAttribute
            );

            // Perform Evaluation
            $result = $expression->evaluate($context, $accessor);

            // Assert False (from TestUnits)
            $this->assertFalse($result);
        }
    }
?>