<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\request;

use mako\http\exceptions\BadRequestException;
use mako\http\request\Body;
use mako\tests\TestCase;

/**
 * @group unit
 */
class BodyTest extends TestCase
{
	/**
	 *
	 */
	public function testJsonBody(): void
	{
		$data =
		[
			'foo' => true,
			'bar' => false,
		];

		$body = new Body(json_encode($data), 'application/json');

		$this->assertSame($data, $body->all());
	}

	/**
	 *
	 */
	public function testInvalidJsonBody(): void
	{
		$this->expectException(BadRequestException::class);

		$body = new Body('', 'application/json');

		$this->assertSame([], $body->all());
	}

	/**
	 *
	 */
	public function testUrlencodedBody(): void
	{
		$data =
		[
			'foo' => '123',
			'bar' => '456',
		];

		$body = new Body(http_build_query($data), 'application/x-www-form-urlencoded');

		$this->assertSame($data, $body->all());
	}

	/**
	 *
	 */
	public function testInvalidUrlencodedBody(): void
	{
		$body = new Body('', 'application/x-www-form-urlencoded');

		$this->assertSame([], $body->all());
	}

	/**
	 *
	 */
	public function testUnsupportedBody(): void
	{
		$body = new Body('foo:bar', 'foo/bar');

		$this->assertSame([], $body->all());
	}
}
