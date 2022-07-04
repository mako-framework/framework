<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\routing\middleware;

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
	public function testWithArray(): void
	{
		$constraint = new Constraint(['foobar', 'barfoo']);

		$this->assertSame(['foobar', 'barfoo'], $constraint->getConstraints());
	}

	/**
	 *
	 */
	public function testWithString(): void
	{
		$constraint = new Constraint('foobar');

		$this->assertSame(['foobar'], $constraint->getConstraints());
	}
}
