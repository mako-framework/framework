<?php

namespace mako\tests\unit\http;

use mako\http\Request;

use \Mockery as m;

/**
 * @group unit
 */

class RequestTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function tearDown()
	{
		m::close();
	}

	/**
	 *
	 */

	public function getServerData()
	{
		return [
			'HTTP_HOST' => 'example.local',
			'HTTP_CONNECTION' => 'keep-alive',
			'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,foo/bar; q=0.1,application/xml;q=0.9,image/webp,*/*;q=0.8',
			'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36',
			'HTTP_DNT' => '1',
			'HTTP_ACCEPT_CHARSET' => 'UTF-8,FOO-1; q=0.1,UTF-16;q=0.9',
			'HTTP_ACCEPT_ENCODING' => 'gzip,foobar;q=0.1,deflate,sdch',
			'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8,da;q=0.6,fr;q=0.4,foo; q=0.1,nb;q=0.2,sv;q=0.2',
			'PATH' => '/usr/local/bin:/usr/bin:/bin',
			'SERVER_SIGNATURE' => '<address>Apache/2.4.6 (Ubuntu) Server at example.local Port 80</address>',
			'SERVER_SOFTWARE' => 'Apache/2.4.6 (Ubuntu)',
			'SERVER_NAME' => 'example.local',
			'SERVER_ADDR' => '10.17.2.9',
			'SERVER_PORT' => '80',
			'REMOTE_ADDR' => '10.17.12.209',
			'DOCUMENT_ROOT' => '/var/www',
			'REQUEST_SCHEME' => 'http',
			'CONTEXT_PREFIX' => '',
			'CONTEXT_DOCUMENT_ROOT' => '/var/www',
			'SERVER_ADMIN' => 'webmaster@localhost',
			'REMOTE_PORT' => '53058',
			'GATEWAY_INTERFACE' => 'CGI/1.1',
			'SERVER_PROTOCOL' => 'HTTP/1.1',
			'REQUEST_METHOD' => 'GET',
			'QUERY_STRING' => '',
			'REQUEST_TIME_FLOAT' => 1398338683.59,
			'REQUEST_TIME' => 1398338683,
			'REQUEST_URI' => '/test/',
			'SCRIPT_NAME' => '/index.php',
			'PHP_SELF' => '/index.php/test/',
			'SCRIPT_FILENAME' => '/var/www/index.php',
			'PATH_INFO' => '/test/',
		];
	}

	/**
	 *
	 */

	public function testAcceptableContentTypes()
	{
		$server = $this->getServerData();

		$acceptableContentTypes = ['text/html', 'application/xhtml+xml', 'image/webp', 'application/xml', '*/*', 'foo/bar'];

		$this->assertEquals($acceptableContentTypes, (new Request(['server' => $server]))->acceptableContentTypes());
	}

	/**
	 *
	 */

	public function testAcceptableLanguages()
	{
		$server = $this->getServerData();

		$acceptableLanguages = ['en-US', 'en', 'da', 'fr', 'nb', 'sv', 'foo'];

		$this->assertEquals($acceptableLanguages, (new Request(['server' => $server]))->acceptableLanguages());
	}

	/**
	 *
	 */

	public function testAcceptableCharsets()
	{
		$server = $this->getServerData();

		$acceptableCharsets = ['UTF-8', 'UTF-16', 'FOO-1'];

		$this->assertEquals($acceptableCharsets, (new Request(['server' => $server]))->acceptableCharsets());
	}

	/**
	 *
	 */

	public function testAcceptableEncodings()
	{
		$server = $this->getServerData();

		$acceptableEncodings = ['gzip', 'deflate', 'sdch', 'foobar'];

		$this->assertEquals($acceptableEncodings, (new Request(['server' => $server]))->acceptableEncodings());
	}

	/**
	 *
	 */

	public function testIP()
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertEquals('10.17.12.209', $request->ip());

		// Should fall back to localhost if an invalid IP is detected

		$server['REMOTE_ADDR'] = 'invalid ip';

		$request = new Request(['server' => $server]);

		$this->assertEquals('127.0.0.1', $request->ip());

		// Should ignore the X-Forwarded-For header if no list of trusted proxies is specified

		$server['REMOTE_ADDR'] = '127.0.0.1';

		$server['HTTP_X_FORWARDED_FOR'] = '10.17.12.211, 10.17.12.212, 10.17.12.213';

		$request = new Request(['server' => $server]);

		$this->assertEquals('127.0.0.1', $request->ip());

		// Should return the IP forwarded by the first trusted proxy

		$request = new Request(['server' => $server]);

		$request->setTrustedProxies(['10.17.12.213']);

		$this->assertEquals('10.17.12.212', $request->ip());

		// Should return the IP forwarded by the first trusted proxy

		$request = new Request(['server' => $server]);

		$request->setTrustedProxies(['10.17.12.212', '10.17.12.213']);

		$this->assertEquals('10.17.12.211', $request->ip());
	}

	/**
	 *
	 */

	public function testIsAjax()
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertFalse($request->isAjax());

		//

		$server['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$request = new Request(['server' => $server]);

		$this->assertTrue($request->isAjax());
	}

	/**
	 *
	 */

	public function testIsSecure()
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertFalse($request->isSecure());

		//

		$server['HTTPS'] = 'off';

		$request = new Request(['server' => $server]);

		$this->assertFalse($request->isSecure());

		//

		$server['HTTPS'] = '0';

		$request = new Request(['server' => $server]);

		$this->assertFalse($request->isSecure());

		//

		$server['HTTPS'] = 'false';

		$request = new Request(['server' => $server]);

		$this->assertFalse($request->isSecure());

		//

		$server['HTTPS'] = 'on';

		$request = new Request(['server' => $server]);

		$this->assertTrue($request->isSecure());

		//

		$server['HTTPS'] = '1';

		$request = new Request(['server' => $server]);

		$this->assertTrue($request->isSecure());

		//

		$server['HTTPS'] = 'true';

		$request = new Request(['server' => $server]);

		$this->assertTrue($request->isSecure());
	}

	/**
	 *
	 */

	public function testBaseURL()
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertEquals('http://example.local', $request->baseURL());

		//

		$server = $this->getServerData();

		$server['HTTPS'] = 'on';

		$request = new Request(['server' => $server]);

		$this->assertEquals('https://example.local', $request->baseURL());

		//

		$server = $this->getServerData();

		$server['HTTPS'] = 'on';

		$server['HTTP_HOST'] = 'example.local:8080';

		$request = new Request(['server' => $server]);

		$this->assertEquals('https://example.local:8080', $request->baseURL());

		//

		$server = $this->getServerData();

		$server['HTTPS'] = 'on';

		unset($server['HTTP_HOST']);

		$request = new Request(['server' => $server]);

		$this->assertEquals('https://example.local', $request->baseURL());

		//

		$server = $this->getServerData();

		$server['HTTPS'] = 'on';

		unset($server['HTTP_HOST']);

		$server['SERVER_PORT'] = '8080';

		$request = new Request(['server' => $server]);

		$this->assertEquals('https://example.local:8080', $request->baseURL());
	}

	/**
	 *
	 */

	public function testPath()
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertEquals('/test/', $request->path());

		//

		unset($server['PATH_INFO']);

		$request = new Request(['server' => $server]);

		$this->assertEquals('/test/', $request->path());

		//

		$request = new Request(['server' => $server, 'path' => '/foo/bar']);

		$this->assertEquals('/foo/bar', $request->path());
	}

	/**
	 *
	 */

	public function testPathWithLanguage()
	{
		$server = $this->getServerData();

		$server['PATH_INFO'] = '/no/test/';

		$request = new Request(['server' => $server, 'languages' => ['no' => 'nb_NO']]);

		$this->assertEquals('/test/', $request->path());
	}

	/**
	 *
	 */

	public function testLanguage()
	{
		$server = $this->getServerData();

		$server['PATH_INFO'] = '/no/test/';

		$request = new Request(['server' => $server, 'languages' => ['no' => 'nb_NO']]);

		$this->assertEquals('nb_NO', $request->language());
	}

	/**
	 *
	 */

	public function testLanguagePrefix()
	{
		$server = $this->getServerData();

		$server['PATH_INFO'] = '/no/test/';

		$request = new Request(['server' => $server, 'languages' => ['no' => 'nb_NO']]);

		$this->assertEquals('no', $request->languagePrefix());
	}

	/**
	 *
	 */

	public function testMethod()
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertEquals('GET', $request->method());

		//

		$request = new Request(['server' => $server, 'method' => 'PATCH']);

		$this->assertEquals('PATCH', $request->method());

		//

		$server['REQUEST_METHOD'] = 'POST';

		$request = new Request(['server' => $server, 'post' => ['REQUEST_METHOD_OVERRIDE' => 'PUT']]);

		$this->assertEquals('PUT', $request->method());

		//

		$server['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'OPTIONS';

		$request = new Request(['server' => $server]);

		$this->assertEquals('OPTIONS', $request->method());
	}

	/**
	 *
	 */

	public function testRealMethod()
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertEquals('GET', $request->realMethod());

		//

		$server['REQUEST_METHOD'] = 'POST';

		$request = new Request(['server' => $server, 'post' => ['REQUEST_METHOD_OVERRIDE' => 'PUT']]);

		$this->assertEquals('POST', $request->realMethod());

		//

		$server['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'OPTIONS';

		$request = new Request(['server' => $server]);

		$this->assertEquals('POST', $request->realMethod());
	}

	/**
	 *
	 */

	public function testIsFaked()
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertFalse($request->isFaked());

		//

		$server['REQUEST_METHOD'] = 'POST';

		$request = new Request(['server' => $server, 'post' => ['REQUEST_METHOD_OVERRIDE' => 'PUT']]);

		$this->assertTrue($request->isFaked());

		//

		$server['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'OPTIONS';

		$request = new Request(['server' => $server]);

		$this->assertTrue($request->isFaked());
	}

	/**
	 *
	 */

	public function testUsername()
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertNull($request->username());

		//

		$server['PHP_AUTH_USER'] = 'foobar';

		$request = new Request(['server' => $server]);

		$this->assertEquals('foobar', $request->username());
	}

	/**
	 *
	 */

	public function testPassword()
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertNull($request->password());

		//

		$server['PHP_AUTH_PW'] = 'foobar';

		$request = new Request(['server' => $server]);

		$this->assertEquals('foobar', $request->password());
	}

	/**
	 *
	 */

	public function testCookie()
	{
		$cookies = ['foo' => 'bar'];

		$request = new Request(['cookies' => $cookies]);

		$this->assertNull($request->cookie('bar'));

		$this->assertFalse($request->cookie('bar', false));

		$this->assertEquals('bar', $request->cookie('foo'));
	}

	/**
	 *
	 */

	public function testSignedCookie()
	{
		$signer = m::mock('\mako\security\Signer');

		$signer->shouldReceive('validate')->withArgs(['bar'])->andReturn('bar');

		$signer->shouldReceive('validate')->withArgs(['bax'])->andReturn(false);

		$cookies = ['foo' => 'bar', 'baz' => 'bax'];

		$request = new Request(['cookies' => $cookies], $signer);

		$this->assertNull($request->signedCookie('bar'));

		$this->assertFalse($request->signedCookie('bar', false));

		$this->assertEquals('bar', $request->signedCookie('foo'));

		$this->assertNull($request->signedCookie('baz'));

		$this->assertFalse($request->signedCookie('baz', false));
	}

	/**
	 * @expectedException \RuntimeException
	 */

	public function testSignedCookieException()
	{
		$request = new Request();

		$request->signedCookie();
	}

	/**
	 *
	 */

	public function testHeader()
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertNull($request->header('bar'));

		$this->assertFalse($request->header('bar', false));

		$this->assertEquals('keep-alive', $request->header('connection'));

		$this->assertEquals('keep-alive', $request->header('ConNeCtIoN'));
	}

	/**
	 *
	 */

	public function testServer()
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertNull($request->server('bar'));

		$this->assertFalse($request->server('bar', false));

		$this->assertEquals('example.local', $request->server('HTTP_HOST'));

		$this->assertEquals($server, $request->server());
	}

	/**
	 *
	 */

	public function testGet()
	{
		$get = ['foo' => 'bar', 'baz' => ['bax']];

		$request = new Request(['get' => $get]);

		$this->assertNull($request->get('bar'));

		$this->assertFalse($request->get('bar', false));

		$this->assertEquals('bar', $request->get('foo'));

		$this->assertEquals('bax', $request->get('baz.0'));

		$this->assertEquals($get, $request->get());
	}

	/**
	 *
	 */

	public function testPost()
	{
		$post = ['foo' => 'bar', 'baz' => ['bax']];

		$request = new Request(['post' => $post]);

		$this->assertNull($request->post('bar'));

		$this->assertFalse($request->post('bar', false));

		$this->assertEquals('bar', $request->post('foo'));

		$this->assertEquals('bax', $request->post('baz.0'));

		$this->assertEquals($post, $request->post());
	}

	/**
	 *
	 */

	public function testBody()
	{
		$request = new Request(['body' => '{"foo":"bar","baz":["bax"]}']);

		$this->assertEquals('{"foo":"bar","baz":["bax"]}', $request->body());
	}

	/**
	 *
	 */

	public function testJsonPutData()
	{
		$body = ['foo' => 'bar', 'baz' => ['bax']];

		$server = $this->getServerData();

		$server['HTTP_CONTENT_TYPE'] = 'application/json';

		$request = new Request(['server' => $server, 'body' => json_encode($body)]);

		$this->assertNull($request->put('bar'));

		$this->assertFalse($request->put('bar', false));

		$this->assertEquals('bar', $request->put('foo'));

		$this->assertEquals('bax', $request->put('baz.0'));

		$this->assertEquals($body, $request->put());
	}

	/**
	 *
	 */

	public function testURLEncodedPutData()
	{
		$body = ['foo' => 'bar', 'baz' => ['bax']];

		$server = $this->getServerData();

		$server['HTTP_CONTENT_TYPE'] = 'application/x-www-form-urlencoded';

		$request = new Request(['server' => $server, 'body' => http_build_query($body)]);

		$this->assertNull($request->put('bar'));

		$this->assertFalse($request->put('bar', false));

		$this->assertEquals('bar', $request->put('foo'));

		$this->assertEquals('bax', $request->put('baz.0'));

		$this->assertEquals($body, $request->put());
	}

	/**
	 *
	 */

	public function testMagicGet()
	{
		$get = ['foo' => 'bar', 'baz' => ['bax']];

		$request = new Request(['get' => $get]);

		$this->assertNull($request->bar);

		$this->assertEquals('bar', $request->foo);

		$this->assertEquals('bax', $request->baz[0]);
	}

	/**
	 *
	 */

	public function testMagicIsset()
	{
		$get = ['foo' => 'bar', 'baz' => ['bax']];

		$request = new Request(['get' => $get]);

		$this->assertFalse(isset($request->bar));

		$this->assertTrue(isset($request->foo));

		$this->assertTrue(isset($request->baz));
	}

	/**
	 *
	 */

	public function testSetRoute()
	{
		$request = new Request();

		$route = m::mock('mako\http\routing\Route');

		$request->setRoute($route);
	}

	/**
	 *
	 */

	public function testGetRoute()
	{
		$request = new Request();

		$this->assertNull($request->getRoute());

		$route = m::mock('mako\http\routing\Route');

		$request->setRoute($route);

		$this->assertSame($route, $request->getRoute());
	}
}