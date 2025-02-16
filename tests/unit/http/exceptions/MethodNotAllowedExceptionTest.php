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
	public function testGetAllowedMethods(): void
	{
		$exception = new MethodNotAllowedException(allowedMethods: ['GET', 'POST']);

		$this->assertSame(['GET', 'POST'], $exception->getAllowedMethods());
	}

	/**
	 *
	 */
	public function testGetHeaders(): void
	{
		$exception = new MethodNotAllowedException(allowedMethods: ['GET', 'POST']);

		$this->assertSame(['Allow' => 'GET,POST'], $exception->getHeaders());
	}

	/**
	 *
	 */
	public function testGetHeadersWithNoAllowedMethods(): void
	{
		$exception = new MethodNotAllowedException;

		$this->assertSame([], $exception->getHeaders());
	}
}
