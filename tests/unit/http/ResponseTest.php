<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http;

use Closure;
use mako\http\Request;
use mako\http\Response;
use mako\http\response\builders\JSON;
use mako\http\response\Cookies;
use mako\http\response\Headers;
use mako\http\response\senders\Redirect;
use mako\http\response\Status;
use mako\tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use ValueError;

#[Group('unit')]
class ResponseTest extends TestCase
{
	/**
	 *
	 */
	public function getRequest(): MockInterface&Request
	{
		$request = Mockery::mock(Request::class);

		return $request;
	}

	/**
	 *
	 */
	protected function getHeaders(): array
	{
		return
		[
			'X-Foo-Bar' => 'foo bar',
			'X-Baz-Bax' => 'baz bax',
		];
	}

	/**
	 *
	 */
	protected function getCookies(): array
	{
		return
		[
			'foo-bar' => 'foo bar',
			'baz-bax' => 'baz bax',
		];
	}

	/**
	 *
	 */
	public function testGetRequest(): void
	{
		$request = $this->getRequest();

		$response = new Response($request);

		$this->assertSame($request, $response->getRequest());
	}

	/**
	 *
	 */
	public function testBody(): void
	{
		$response = new Response($this->getRequest());

		$response->setBody('Hello, world!');

		$this->assertEquals('Hello, world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testBodyWithSender(): void
	{
		$response = new Response($this->getRequest());

		$response->setBody(new Redirect('foobar'));

		$this->assertInstanceOf(Redirect::class, $response->getBody());
	}

	/**
	 *
	 */
	public function testBodyWithBuilder(): void
	{
		$response = new Response($this->getRequest());

		$response->setBody(new JSON('foobar'));

		$this->assertInstanceOf(JSON::class, $response->getBody());
	}

	/**
	 *
	 */
	public function testClearBody(): void
	{
		$response = new Response($this->getRequest());

		$response->setBody('Hello, world!');

		$response->clearBody();

		$this->assertNull($response->getBody());
	}

	/**
	 *
	 */
	public function testType(): void
	{
		$response = new Response($this->getRequest());

		$this->assertEquals('text/html', $response->getType());

		//

		$response = new Response($this->getRequest());

		$response->setType('application/json');

		$this->assertEquals('application/json', $response->getType());
	}

	/**
	 *
	 */
	public function testTypeWithCharset(): void
	{
		$response = new Response($this->getRequest());

		$response->setType('application/json', 'iso-8859-1');

		$this->assertEquals('application/json', $response->getType());

		$this->assertEquals('iso-8859-1', $response->getCharset());
	}

	/**
	 *
	 */
	public function testCharset(): void
	{
		$response = new Response($this->getRequest());

		$this->assertEquals('UTF-8', $response->getCharset());

		//

		$response = new Response($this->getRequest());

		$response->setCharset('iso-8859-1');

		$this->assertEquals('iso-8859-1', $response->getCharset());
	}

	/**
	 *
	 */
	public function testDefaultStatus(): void
	{
		$response = new Response($this->getRequest());

		$this->assertEquals(Status::OK, $response->getStatus());

	}

	/**
	 *
	 */
	public function testValidStatus(): void
	{
		$response = new Response($this->getRequest());

		$response->setStatus(404);

		$this->assertEquals(Status::NOT_FOUND, $response->getStatus());

	}

	/**
	 *
	 */
	public function testInvaludStatus(): void
	{
		$this->expectException(ValueError::class);
		$this->expectExceptionMessageMatches('/^999 is not a valid backing value for enum/');

		$response = new Response($this->getRequest());

		$response->setStatus(999);
	}

	/**
	 *
	 */
	public function testClear(): void
	{
		$response = new Response($this->getRequest());

		$response->setBody('Hello, world!');

		foreach ($this->getHeaders() as $header => $value) {
			$response->getHeaders()->add($header, $value);
		}

		foreach ($this->getCookies() as $cookie => $value) {
			$response->getCookies()->add($cookie, $value);
		}

		$this->assertCount(2, $response->getHeaders());

		$this->assertCount(2, $response->getCookies());

		$this->assertInstanceOf(Response::class, $response->clear());

		$this->assertNull($response->getBody());

		$this->assertCount(0, $response->getHeaders());

		$this->assertCount(0, $response->getCookies());
	}

	/**
	 *
	 */
	public function testClearExcept(): void
	{
		$response = new Response($this->getRequest());

		$response->setBody('Hello, world!');

		foreach ($this->getHeaders() as $header => $value) {
			$response->getHeaders()->add($header, $value);
		}

		foreach ($this->getCookies() as $cookie => $value) {
			$response->getCookies()->add($cookie, $value);
		}

		$this->assertCount(2, $response->getHeaders());

		$this->assertCount(2, $response->getCookies());

		$this->assertInstanceOf(Response::class, $response->clearExcept(['headers' => ['X-Foo-.*'], 'cookies' => ['foo-.*']]));

		$this->assertNull($response->getBody());

		$this->assertCount(1, $response->getHeaders());

		$this->assertCount(1, $response->getCookies());
	}

	/**
	 *
	 */
	public function testClearExceptWithNoExceptions(): void
	{
		$response = new Response($this->getRequest());

		$response->setBody('Hello, world!');

		foreach ($this->getHeaders() as $header => $value) {
			$response->getHeaders()->add($header, $value);
		}

		foreach ($this->getCookies() as $cookie => $value) {
			$response->getCookies()->add($cookie, $value);
		}

		$this->assertCount(2, $response->getHeaders());

		$this->assertCount(2, $response->getCookies());

		$this->assertInstanceOf(Response::class, $response->clearExcept([]));

		$this->assertNull($response->getBody());

		$this->assertCount(0, $response->getHeaders());

		$this->assertCount(0, $response->getCookies());
	}

	/**
	 *
	 */
	public function testReset(): void
	{
		$response = new Response($this->getRequest());

		$response->setBody('Hello, world!');

		foreach ($this->getHeaders() as $header => $value) {
			$response->getHeaders()->add($header, $value);
		}

		foreach ($this->getCookies() as $cookie => $value) {
			$response->getCookies()->add($cookie, $value);
		}

		$response->setStatus(404);

		$this->assertCount(2, $response->getHeaders());

		$this->assertCount(2, $response->getCookies());

		$this->assertInstanceOf(Response::class, $response->reset());

		$this->assertNull($response->getBody());

		$this->assertCount(0, $response->getHeaders());

		$this->assertCount(0, $response->getCookies());

		$this->assertSame(Status::OK, $response->getStatus());
	}

	/**
	 *
	 */
	public function testResetExcept(): void
	{
		$response = new Response($this->getRequest());

		$response->setBody('Hello, world!');

		foreach ($this->getHeaders() as $header => $value) {
			$response->getHeaders()->add($header, $value);
		}

		foreach ($this->getCookies() as $cookie => $value) {
			$response->getCookies()->add($cookie, $value);
		}

		$response->setStatus(404);

		$this->assertCount(2, $response->getHeaders());

		$this->assertCount(2, $response->getCookies());

		$this->assertInstanceOf(Response::class, $response->resetExcept(['headers' => ['X-Foo-.*'], 'cookies' => ['foo-.*']]));

		$this->assertNull($response->getBody());

		$this->assertCount(1, $response->getHeaders());

		$this->assertCount(1, $response->getCookies());

		$this->assertSame(Status::OK, $response->getStatus());
	}

	/**
	 *
	 */
	public function testResetExceptWithNoExceptions(): void
	{
		$response = new Response($this->getRequest());

		$response->setBody('Hello, world!');

		foreach ($this->getHeaders() as $header => $value) {
			$response->getHeaders()->add($header, $value);
		}

		foreach ($this->getCookies() as $cookie => $value) {
			$response->getCookies()->add($cookie, $value);
		}

		$response->setStatus(404);

		$this->assertCount(2, $response->getHeaders());

		$this->assertCount(2, $response->getCookies());

		$this->assertInstanceOf(Response::class, $response->resetExcept([]));

		$this->assertNull($response->getBody());

		$this->assertCount(0, $response->getHeaders());

		$this->assertCount(0, $response->getCookies());

		$this->assertSame(Status::OK, $response->getStatus());
	}

	/**
	 *
	 */
	public function testIsCacheable(): void
	{
		$request = $this->getRequest();

		$request->shouldReceive('isCacheable')->once()->andReturn(true);

		$response = new Response($request);

		$this->assertTrue($response->isCacheable());

		//

		$request = $this->getRequest();

		$request->shouldReceive('isCacheable')->once()->andReturn(false);

		$response = new Response($request);

		$this->assertFalse($response->isCacheable());

		//

		$request = $this->getRequest();

		$request->shouldReceive('isCacheable')->once()->andReturn(true);

		$response = new Response($request);

		$response->setStatus(400);

		$this->assertFalse($response->isCacheable());

		//

		$request = $this->getRequest();

		$request->shouldReceive('isCacheable')->once()->andReturn(true);

		$response = new Response($request);

		$response->getHeaders()->add('Cache-Control', 'private');

		$this->assertFalse($response->isCacheable());

		//

		$request = $this->getRequest();

		$request->shouldReceive('isCacheable')->once()->andReturn(true);

		$response = new Response($request);

		$response->getHeaders()->add('Cache-Control', 'no-store');

		$this->assertFalse($response->isCacheable());
	}

	/**
	 *
	 */
	public function testCookies(): void
	{
		$response = new Response($this->getRequest());

		$this->assertInstanceOf(Cookies::class, $response->getCookies());
		$this->assertInstanceOf(Cookies::class, $response->cookies);

		$this->assertFalse($response->cookies->has('foo'));

		$response->cookies->add('foo', 'bar');

		$this->assertTrue($response->cookies->has('foo'));
	}

	/**
	 *
	 */
	public function testHeaders(): void
	{
		$response = new Response($this->getRequest());

		$this->assertInstanceOf(Headers::class, $response->getHeaders());
		$this->assertInstanceOf(Headers::class, $response->headers);

		$this->assertFalse($response->headers->has('X-Foo'));

		$response->headers->add('X-Foo', 'bar');

		$this->assertTrue($response->headers->has('X-Foo'));
	}

	/**
	 *
	 */
	public function testGetCompressionHandler(): void
	{
		$response = new Response($this->getRequest());

		$this->assertSame('ob_gzhandler', $response->getCompressionHandler());
	}

	/**
	 *
	 */
	public function testSetCompressionHandler(): void
	{
		$response = new Response($this->getRequest());

		$response->setCompressionHandler(fn (string $buffer, int $phase): string => $buffer);

		$this->assertInstanceOf(Closure::class, $response->getCompressionHandler());
	}
}
