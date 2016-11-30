<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\reactor\exceptions;

use PHPUnit_Framework_TestCase;

use mako\reactor\exceptions\MissingOptionException;

/**
 * @group unit
 */
class MissingOptionExceptionTest extends PHPUnit_Framework_TestCase
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
