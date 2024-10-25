<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\exceptions;

use mako\http\exceptions\MethodNotAllowedException;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class MethodNotAllowedExceptionTest extends TestCase
{
	/**
	 *
	 */
	public function testFailedConnection(): void
	{
		$exception = new MethodNotAllowedException(['GET', 'POST']);

		$this->assertSame(['GET', 'POST'], $exception->getAllowedMethods());
	}
}
