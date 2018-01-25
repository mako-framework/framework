<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\reactor\exceptions;

use mako\reactor\exceptions\InvalidOptionException;
use mako\tests\TestCase;

/**
 * @group unit
 */
class InvalidOptionExceptionTest extends TestCase
{
	/**
	 *
	 */
	public function testException()
	{
		$exception = new InvalidOptionException('foo', 'bar', 'baz');

		$this->assertEquals('foo', $exception->getMessage());

		$this->assertEquals('bar', $exception->getName());

		$this->assertEquals('baz', $exception->getSuggestion());
	}
}
