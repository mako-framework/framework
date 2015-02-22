<?php

namespace mako\tests\unit\http\exceptions;

use mako\http\exceptions\MethodNotAllowedException;

use PHPUnit_Framework_TestCase;

/**
 * @group unit
 */

class MethodNotAllowedExceptionTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function testFailedConnection()
	{
		$exception = new MethodNotAllowedException(['GET', 'POST']);

		$this->assertSame(['GET', 'POST'], $exception->getAllowedMethods());
	}
}