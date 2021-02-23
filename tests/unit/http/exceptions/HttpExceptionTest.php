<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\exceptions;

use mako\http\exceptions\HttpException;
use mako\tests\TestCase;

/**
 * @group unit
 */
class HttpExceptionTest extends TestCase
{
	/**
	 *
	 */
	public function testGetCodeAndGetMessage(): void
	{
		$exception = new HttpException(500);

		$this->assertSame(500, $exception->getCode());

		$this->assertSame('', $exception->getMessage());

		//

		$exception = new HttpException(500, 'Server Error');

		$this->assertSame(500, $exception->getCode());

		$this->assertSame('Server Error', $exception->getMessage());
	}

	/**
	 *
	 */
	public function testSetAndGetMetadata(): void
	{
		$exception = new HttpException(500);

		$data = ['foo' => 'bar'];

		$this->assertSame([], $exception->getMetadata());

		$this->assertInstanceOf(HttpException::class, $exception->setMetadata($data));

		$this->assertSame($data, $exception->getMetadata());
	}
}
