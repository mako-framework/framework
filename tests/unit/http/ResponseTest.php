<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http;

use mako\http\Request;
use mako\http\Response;
use mako\http\response\builders\JSON;
use mako\http\response\senders\Redirect;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class ResponseTest extends TestCase
{
	/**
	 *
	 */
	public function getRequest()
	{
		$request = Mockery::mock(Request::class);

		return $request;
	}

	/**
	 *
	 */
	public function getHeaders()
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

		$this->assertInstanceOf('\mako\http\response\senders\Redirect', $response->getBody());
	}

	/**
	 *
	 */
	public function testBodyWithBuilder(): void
	{
		$response = new Response($this->getRequest());

		$response->setBody(new JSON('foobar'));

		$this->assertInstanceOf('\mako\http\response\builders\JSON', $response->getBody());
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
	public function testStatus(): void
	{
		$response = new Response($this->getRequest());

		$this->assertEquals(200, $response->getStatus());

		//

		$response = new Response($this->getRequest());

		$response->setStatus(404);

		$this->assertEquals(404, $response->getStatus());

		//

		$response = new Response($this->getRequest());

		$response->setStatus(999); // Invalid status code

		$this->assertEquals(200, $response->getStatus());
	}

	/**
	 *
	 */
	public function testClear(): void
	{
		$response = new Response($this->getRequest());

		$response->setBody('Hello, world!');

		foreach($this->getHeaders() as $header => $value)
		{
			$response->getHeaders()->add($header, $value);
		}

		$response->getCookies()->add('foo', 'foo cookie');

		$this->assertInstanceOf(Response::class, $response->clear());

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

		foreach($this->getHeaders() as $header => $value)
		{
			$response->getHeaders()->add($header, $value);
		}

		$response->getCookies()->add('foo', 'foo cookie');

		$response->setStatus(404);

		$this->assertInstanceOf(Response::class, $response->reset());

		$this->assertNull($response->getBody());

		$this->assertCount(0, $response->getHeaders());

		$this->assertCount(0, $response->getCookies());

		$this->assertSame(200, $response->getStatus());
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
}
