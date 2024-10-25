<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\exceptions;

use mako\http\exceptions\HttpStatusException;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class HttpExceptionTest extends TestCase
{
	/**
	 *
	 */
	public function testGetCodeAndGetMessage(): void
	{
		$exception = new HttpStatusException(500);

		$this->assertSame(500, $exception->getCode());

		$this->assertSame('', $exception->getMessage());

		//

		$exception = new HttpStatusException(500, 'Server Error');

		$this->assertSame(500, $exception->getCode());

		$this->assertSame('Server Error', $exception->getMessage());
	}

	/**
	 *
	 */
	public function testSetAndGetMetadata(): void
	{
		$exception = new HttpStatusException(500);

		$data = ['foo' => 'bar'];

		$this->assertSame([], $exception->getMetadata());

		$this->assertInstanceOf(HttpStatusException::class, $exception->setMetadata($data));

		$this->assertSame($data, $exception->getMetadata());
	}
}
