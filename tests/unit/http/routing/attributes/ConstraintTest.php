<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\routing\attributes;

use mako\http\routing\attributes\Constraint;
use mako\tests\TestCase;

/**
 * @group unit
 */
class ConstraintTest extends TestCase
{
	/**
	 *
	 */
	public function testGetConstraint(): void
	{
		$constraint = new Constraint(FooBar::class);

		$this->assertSame(FooBar::class, $constraint->getConstraint());
	}

	/**
	 *
	 */
	public function testGetParameters(): void
	{
		$constraint = new Constraint(FooBar::class, foo: 'bar');

		$this->assertSame(['foo' => 'bar'], $constraint->getParameters());
	}

	/**
	 *
	 */
	public function testGetConstraintAndParameters(): void
	{
		$constraint = new Constraint(FooBar::class, foo: 'bar');

		$this->assertSame(['constraint' => FooBar::class, 'parameters' => ['foo' => 'bar']], $constraint->getConstraintAndParameters());
	}
}
