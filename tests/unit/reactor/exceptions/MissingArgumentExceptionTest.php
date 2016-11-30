<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\reactor\exceptions;

use PHPUnit_Framework_TestCase;

use mako\reactor\exceptions\MissingArgumentException;

/**
 * @group unit
 */
class MissingArgumentExceptionTest extends PHPUnit_Framework_TestCase
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
