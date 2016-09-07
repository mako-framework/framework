<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\reactor\exceptions;

use PHPUnit_Framework_TestCase;

use mako\reactor\exceptions\InvalidOptionException;

/**
 * @group unit
 */
class InvalidOptionExceptionTest extends PHPUnit_Framework_TestCase
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