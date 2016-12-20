<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\reactor\exceptions;

use PHPUnit_Framework_TestCase;

use mako\reactor\exceptions\ArgumentException;

/**
 * @group unit
 */
class ArgumentExceptionTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function testException()
	{
		$exception = new ArgumentException('foo', 'bar');

		$this->assertEquals('foo', $exception->getMessage());

		$this->assertEquals('bar', $exception->getName());
	}
}
