<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\exceptions;

use DateTime;
use mako\http\exceptions\TooManyRequestsException;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class TooManyRequestsExceptionTest extends TestCase
{
	/**
	 *
	 */
	public function testGetAllowedMethods(): void
	{
		$exception = new TooManyRequestsException;

		$this->assertSame([], $exception->getHeaders());
	}

	/**
	 *
	 */
	public function testGetRetryAfter(): void
	{
		$exception = new TooManyRequestsException;

		$this->assertNull($exception->getRetryAfter());

		//

		$now = new DateTime;

		$exception = new TooManyRequestsException(retryAfter: $now);

		$this->assertSame($now, $exception->getRetryAfter());
	}

	/**
	 *
	 */
	public function testGetHeaders(): void
	{
		$now = new DateTime;

		$exception = new TooManyRequestsException(retryAfter: $now);

		$this->assertSame(['Retry-After' => $now->format('D, d M Y H:i:s \G\M\T')], $exception->getHeaders());
	}
}
