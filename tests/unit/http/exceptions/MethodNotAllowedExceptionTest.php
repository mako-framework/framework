<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\exceptions;

use PHPUnit_Framework_TestCase;

use mako\http\exceptions\MethodNotAllowedException;

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