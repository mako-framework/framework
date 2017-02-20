<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\http\Response;
use mako\http\response\builders\JSON;
use mako\http\response\senders\Redirect;

/**
 * @group unit
 */
class ResponseTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function tearDown()
	{
		Mockery::close();
	}

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
	public function testFilter()
	{
		$response = new Response($this->getRequest());

		$response->filter(function($body)
		{
			return '';
		});

		$response->filter(function($body)
		{
			return '';
		});

		$this->assertTrue(is_array($response->getFilters()));

		$this->assertCount(2, $response->getFilters());

		$this->assertContainsOnlyInstancesOf('\Closure', $response->getFilters());
	}

	/**
	 *
	 */
	public function testClearFilters()
	{
		$response = new Response($this->getRequest());

		$response->filter(function($body)
		{
			return '';
		});

		$response->filter(function($body)
		{
			return '';
		});

		$response->clearFilters();

		$this->assertCount(0, $response->getFilters());
	}

	/**
	 *
	 */
	public function testHeader()
	{
		$response = new Response($this->getRequest());

		foreach($this->getHeaders() as $header => $value)
		{
			$response->header($header, $value);
		}

		$headers = $response->getHeaders();

		$this->assertTrue(is_array($headers));

		$this->assertCount(2, $headers);

		$this->assertArrayHasKey('X-Foo-Bar', $headers);

		$this->assertArrayHasKey('X-Baz-Bax', $headers);

		$this->assertEquals(['foo bar'], $headers['X-Foo-Bar']);

		$this->assertEquals(['baz bax'], $headers['X-Baz-Bax']);
	}

	/**
	 *
	 */
	public function testMultipleHeadersWithTheSameName()
	{
		$response = new Response($this->getRequest());

		$response->header('foo', 'foo1');

		$response->header('foo', 'foo2');

		$response->header('bar', 'bar1', false);

		$response->header('bar', 'bar2', false);

		$headers = $response->getHeaders();

		$this->assertTrue(is_array($headers));

		$this->assertCount(2, $headers);

		$this->assertArrayHasKey('foo', $headers);

		$this->assertArrayHasKey('bar', $headers);

		$this->assertEquals(['foo2'], $headers['foo']);

		$this->assertEquals(['bar1', 'bar2'], $headers['bar']);
	}

	/**
	 *
	 */
	public function testHasHeader()
	{
		$response = new Response($this->getRequest());

		$response->header('foo', 'foo1');

		$this->assertTrue($response->hasHeader('foo'));

		$this->assertFalse($response->hasHeader('bar'));
	}

	/**
	 *
	 */
	public function testRemoveHeader()
	{
		$response = new Response($this->getRequest());

		foreach($this->getHeaders() as $header => $value)
		{
			$response->header($header, $value);
		}

		$response->removeHeader('x-foo-bar');

		$headers = $response->getHeaders();

		$this->assertCount(1, $headers);

		$this->assertArrayHasKey('X-Baz-Bax', $headers);
	}

	/**
	 *
	 */
	public function testClearHeaders()
	{
		$response = new Response($this->getRequest());

		foreach($this->getHeaders() as $header => $value)
		{
			$response->header($header, $value);
		}

		$response->clearHeaders();

		$headers = $response->getHeaders();

		$this->assertCount(0, $headers);
	}

	/**
	 *
	 */
	public function testCookie()
	{
		$response = new Response($this->getRequest());

		$response->cookie('foo', 'foo cookie');

		$response->cookie('faa', 'faa cookie', 3600);

		$response->cookie('bar', 'bar cookie', 0, ['path' => '/bar']);

		$response->cookie('baz', 'baz cookie', 0, ['domain' => '.example.org']);

		$response->cookie('bax', 'bax cookie', 0, ['secure' => true]);

		$response->cookie('bam', 'bam cookie', 0, ['httponly' => true]);

		$cookies = $response->getCookies();

		$this->assertTrue(is_array($cookies));

		$this->assertCount(6, $cookies);

		$this->assertArrayHasKey('foo', $cookies);

		$this->assertArrayHasKey('faa', $cookies);

		$this->assertArrayHasKey('bar', $cookies);

		$this->assertArrayHasKey('baz', $cookies);

		$this->assertArrayHasKey('bax', $cookies);

		$this->assertArrayHasKey('bam', $cookies);

		$this->assertEquals(['name' => 'foo', 'value' => 'foo cookie', 'ttl' => 0, 'path' => '/', 'domain' => '', 'secure' => false, 'httponly' => false], $cookies['foo']);

		$this->assertTrue($cookies['faa']['ttl'] >= (time() - 3600));

		$this->assertEquals(['name' => 'bar', 'value' => 'bar cookie', 'ttl' => 0, 'path' => '/bar', 'domain' => '', 'secure' => false, 'httponly' => false], $cookies['bar']);

		$this->assertEquals(['name' => 'baz', 'value' => 'baz cookie', 'ttl' => 0, 'path' => '/', 'domain' => '.example.org', 'secure' => false, 'httponly' => false], $cookies['baz']);

		$this->assertEquals(['name' => 'bax', 'value' => 'bax cookie', 'ttl' => 0, 'path' => '/', 'domain' => '', 'secure' => true, 'httponly' => false], $cookies['bax']);

		$this->assertEquals(['name' => 'bam', 'value' => 'bam cookie', 'ttl' => 0, 'path' => '/', 'domain' => '', 'secure' => false, 'httponly' => true], $cookies['bam']);
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testSignedCookieWithoutSigner()
	{
		$response = new Response($this->getRequest());

		$response->signedCookie('foo', 'foo cookie');
	}

	/**
	 *
	 */
	public function testSignedCookie()
	{
		$signer = Mockery::mock('\mako\security\Signer');

		$signer->shouldReceive('sign')->andReturn('signed_cookie_value');

		$response = new Response($this->getRequest(), 'UTF8', $signer);

		$response->signedCookie('foo', 'foo cookie');

		$cookies = $response->getCookies();

		$this->assertEquals('signed_cookie_value', $cookies['foo']['value']);
	}

	/**
	 *
	 */
	public function testDeleteCookie()
	{
		$response = new Response($this->getRequest());

		$response->deleteCookie('foo');

		$response->deleteCookie('bar', ['path' => '/bar']);

		$cookies = $response->getCookies();

		$this->assertTrue($cookies['foo']['ttl'] < time());

		$this->assertEquals($cookies['bar']['path'], '/bar');
	}

	/**
	 *
	 */
	public function testHasCookie()
	{
		$response = new Response($this->getRequest());

		$response->cookie('foo', 'foo cookie');

		$this->assertTrue($response->hasCookie('foo'));

		$this->assertFalse($response->hasCookie('bar'));
	}

	/**
	 *
	 */
	public function testRemoveCookie()
	{
		$response = new Response($this->getRequest());

		$response->cookie('foo', 'foo cookie');

		$response->cookie('faa', 'faa cookie', 3600);

		$cookies = $response->getCookies();

		$this->assertCount(2, $cookies);

		$response->removeCookie('foo');

		$cookies = $response->getCookies();

		$this->assertCount(1, $cookies);

		$this->assertFalse(isset($cookies['foo']));

		$this->assertTrue(isset($cookies['faa']));
	}

	/**
	 *
	 */
	public function testClearCookies()
	{
		$response = new Response($this->getRequest());

		$response->cookie('foo', 'foo cookie');

		$response->cookie('faa', 'faa cookie', 3600);

		$response->clearCookies();

		$cookies = $response->getCookies();

		$this->assertCount(0, $cookies);
	}

	/**
	 *
	 */
	public function testClear()
	{
		$response = new Response($this->getRequest());

		$response->body('Hello, world!');

		$response->filter(function($body)
		{
			return '';
		});

		foreach($this->getHeaders() as $header => $value)
		{
			$response->header($header, $value);
		}

		$response->cookie('foo', 'foo cookie');

		$response->clear();

		$this->assertNull($response->getBody());

		$this->assertCount(0, $response->getFilters());

		$this->assertCount(0, $response->getHeaders());

		$this->assertCount(0, $response->getCookies());
	}
}
