<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\reactor\exceptions;

use mako\reactor\exceptions\ArgumentException;
use mako\tests\TestCase;

/**
 * @group unit
 */
class ArgumentExceptionTest extends TestCase
{
	/**
	 *
	 */
	public function testException(): void
	{
		$exception = new ArgumentException('foo', 'bar');

		$this->assertEquals('foo', $exception->getMessage());

		$this->assertEquals('bar', $exception->getName());
	}
}
