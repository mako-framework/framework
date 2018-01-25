<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\reactor\exceptions;

use mako\reactor\exceptions\MissingArgumentException;
use mako\tests\TestCase;

/**
 * @group unit
 */
class MissingArgumentExceptionTest extends TestCase
{
	/**
	 *
	 */
	public function testException()
	{
		$exception = new MissingArgumentException('foo', 'bar');

		$this->assertEquals('foo', $exception->getMessage());

		$this->assertEquals('bar', $exception->getName());
	}
}
