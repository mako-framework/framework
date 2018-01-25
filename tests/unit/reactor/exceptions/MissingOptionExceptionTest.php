<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\reactor\exceptions;

use mako\reactor\exceptions\MissingOptionException;
use mako\tests\TestCase;

/**
 * @group unit
 */
class MissingOptionExceptionTest extends TestCase
{
	/**
	 *
	 */
	public function testException()
	{
		$exception = new MissingOptionException('foo', 'bar');

		$this->assertEquals('foo', $exception->getMessage());

		$this->assertEquals('bar', $exception->getName());
	}
}
