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
	 *{@inheritDoc}
	 */
	public function setup(): void
	{
		if(PHP_VERSION_ID < 80000)
		{
			$this->markTestSkipped('This feature requires PHP 8.0+');
		}
	}

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
