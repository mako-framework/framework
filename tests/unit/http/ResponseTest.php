<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http;

use Mockery;

use mako\http\Response;
use mako\http\response\builders\JSON;
use mako\http\response\senders\Redirect;
use mako\tests\TestCase;

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
		$request = Mockery::mock('\mako\http\Request');

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
	public function testBody()
	{
		$response = new Response($this->getRequest());

		$response->body('Hello, world!');

		$this->assertEquals('Hello, world!', $response->getBody());
	}

	/**
	 *
	 */
	public function testBodyWithSender()
	{
		$response = new Response($this->getRequest());

		$response->body(new Redirect('foobar'));

		$this->assertInstanceOf('\mako\http\response\senders\Redirect', $response->getBody());
	}

	/**
	 *
	 */
	public function testBodyWithBuilder()
	{
		$response = new Response($this->getRequest());

		$response->body(new JSON('foobar'));

		$this->assertInstanceOf('\mako\http\response\builders\JSON', $response->getBody());
	}

	/**
	 *
	 */
	public function testClearBody()
	{
		$response = new Response($this->getRequest());

		$response->body('Hello, world!');

		$response->clearBody();

		$this->assertNull($response->getBody());
	}

	/**
	 *
	 */
	public function testType()
	{
		$response = new Response($this->getRequest());

		$this->assertEquals('text/html', $response->getType());

		//

		$response = new Response($this->getRequest());

		$response->type('application/json');

		$this->assertEquals('application/json', $response->getType());
	}

	/**
	 *
	 */
	public function testTypeWithCharset()
	{
		$response = new Response($this->getRequest());

		$response->type('application/json', 'iso-8859-1');

		$this->assertEquals('application/json', $response->getType());

		$this->assertEquals('iso-8859-1', $response->getCharset());
	}

	/**
	 *
	 */
	public function testCharset()
	{
		$response = new Response($this->getRequest());

		$this->assertEquals('UTF-8', $response->getCharset());

		//

		$response = new Response($this->getRequest());

		$response->charset('iso-8859-1');

		$this->assertEquals('iso-8859-1', $response->getCharset());
	}

	/**
	 *
	 */
	public function testStatus()
	{
		$response = new Response($this->getRequest());

		$this->assertEquals(200, $response->getStatus());

		//

		$response = new Response($this->getRequest());

		$response->status(404);

		$this->assertEquals(404, $response->getStatus());

		//

		$response = new Response($this->getRequest());

		$response->status(999); // Invalid status code

		$this->assertEquals(200, $response->getStatus());
	}

	/**
	 *
	 */
	public function testClear()
	{
		$response = new Response($this->getRequest());

		$response->body('Hello, world!');

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
	public function testReset()
	{
		$response = new Response($this->getRequest());

		$response->body('Hello, world!');

		foreach($this->getHeaders() as $header => $value)
		{
			$response->getHeaders()->add($header, $value);
		}

		$response->getCookies()->add('foo', 'foo cookie');

		$response->status(404);

		$this->assertInstanceOf(Response::class, $response->reset());

		$this->assertNull($response->getBody());

		$this->assertCount(0, $response->getHeaders());

		$this->assertCount(0, $response->getCookies());

		$this->assertSame(200, $response->getStatus());
	}
}
