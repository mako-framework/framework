<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\http\Request;

/**
 * @group unit
 */
class RequestTest extends PHPUnit_Framework_TestCase
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
	public function getServerData()
	{
		return
		[
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
			'REQUEST_URI' => '/index.php/test/',
			'SCRIPT_NAME' => '/index.php',
			'PHP_SELF' => '/index.php/test/',
			'SCRIPT_FILENAME' => '/var/www/index.php',
			'PATH_INFO' => '/test/',
		];
	}

	/**
	 *
	 */
	public function testScriptName()
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertEquals('index.php', $request->scriptName());

		//

		$server['SCRIPT_FILENAME'] = '/var/www/app.php';

		$request = new Request(['server' => $server]);

		$this->assertEquals('app.php', $request->scriptName());
	}

	/**
	 *
	 */
	public function testAcceptableContentTypes()
	{
		$server = $this->getServerData();

		$acceptableContentTypes = ['text/html', 'application/xhtml+xml', 'image/webp', 'application/xml', '*/*', 'foo/bar'];

		$this->assertEquals($acceptableContentTypes, (new Request(['server' => $server]))->getHeaders()->acceptableContentTypes());
	}

	/**
	 *
	 */
	public function testAcceptableLanguages()
	{
		$server = $this->getServerData();

		$acceptableLanguages = ['en-US', 'en', 'da', 'fr', 'nb', 'sv', 'foo'];

		$this->assertEquals($acceptableLanguages, (new Request(['server' => $server]))->getHeaders()->acceptableLanguages());
	}

	/**
	 *
	 */
	public function testAcceptableCharsets()
	{
		$server = $this->getServerData();

		$acceptableCharsets = ['UTF-8', 'UTF-16', 'FOO-1'];

		$this->assertEquals($acceptableCharsets, (new Request(['server' => $server]))->getHeaders()->acceptableCharsets());
	}

	/**
	 *
	 */
	public function testAcceptableEncodings()
	{
		$server = $this->getServerData();

		$acceptableEncodings = ['gzip', 'deflate', 'sdch', 'foobar'];

		$this->assertEquals($acceptableEncodings, (new Request(['server' => $server]))->getHeaders()->acceptableEncodings());
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

		$server['HTTP_X_FORWARDED_FOR'] = '10.17.13.0, 10.17.13.1, 10.17.12.212, 10.17.12.213';

		$request = new Request(['server' => $server]);

		$this->assertEquals('127.0.0.1', $request->ip());

		// Should return the IP forwarded by the first trusted proxy

		$request = new Request(['server' => $server]);

		$request->setTrustedProxies(['10.17.12.213']);

		$this->assertEquals('10.17.12.212', $request->ip());

		// Should return the IP forwarded by the first trusted proxy

		$request = new Request(['server' => $server]);

		$request->setTrustedProxies(['10.17.12.212', '10.17.12.213']);

		$this->assertEquals('10.17.13.1', $request->ip());

		// Should return the IP forwarded by the first trusted proxy

		$request = new Request(['server' => $server]);

		$request->setTrustedProxies(['10.17.12.0/24']);

		$this->assertEquals('10.17.13.1', $request->ip());
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

		$server['REQUEST_URI'] = '/app.php/test/';
		$server['SCRIPT_NAME'] = '/app.php';
		$server['SCRIPT_FILENAME'] = '/var/www/app.php';

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

		$request = new Request(['server' => $server, 'languages' => ['no' => ['strings' => 'nb_NO', 'locale' => [LC_ALL => ['nb_NO.UTF-8', 'nb_NO.utf8', 'C'], LC_NUMERIC => 'C']]]]);

		$this->assertEquals('/test/', $request->path());
	}

	/**
	 *
	 */
	public function testIsClean()
	{
		$request = new Request(['server' => ['SCRIPT_NAME' => '/index.php', 'REQUEST_URI' => '/index.php/foo']]);

		$this->assertFalse($request->isClean());

		//

		$request = new Request(['server' => ['SCRIPT_NAME' => '/index.php', 'REQUEST_URI' => '/foo']]);

		$this->assertTrue($request->isClean());
	}

	/**
	 *
	 */
	public function testLanguage()
	{
		$server = $this->getServerData();

		$server['PATH_INFO'] = '/no/test/';

		$request = new Request(['server' => $server, 'languages' => ['no' => ['strings' => 'nb_NO', 'locale' => [LC_ALL => ['nb_NO.UTF-8', 'nb_NO.utf8', 'C'], LC_NUMERIC => 'C']]]]);

		$this->assertEquals(['strings' => 'nb_NO', 'locale' => [LC_ALL => ['nb_NO.UTF-8', 'nb_NO.utf8', 'C'], LC_NUMERIC => 'C']], $request->language());
	}

	/**
	 *
	 */
	public function testLanguagePrefix()
	{
		$server = $this->getServerData();

		$server['PATH_INFO'] = '/no/test/';

		$request = new Request(['server' => $server, 'languages' => ['no' => ['strings' => 'nb_NO', 'locale' => [LC_ALL => ['nb_NO.UTF-8', 'nb_NO.utf8', 'C'], LC_NUMERIC => 'C']]]]);

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

		$this->assertNull($request->getCookies()->get('bar'));

		$this->assertFalse($request->getCookies()->get('bar', false));

		$this->assertEquals('bar', $request->getCookies()->get('foo'));
	}

	/**
	 *
	 */
	public function testSignedCookie()
	{
		$signer = Mockery::mock('\mako\security\Signer');

		$signer->shouldReceive('validate')->withArgs(['bar'])->andReturn('bar');

		$signer->shouldReceive('validate')->withArgs(['bax'])->andReturn(false);

		$cookies = ['foo' => 'bar', 'baz' => 'bax'];

		$request = new Request(['cookies' => $cookies], $signer);

		$this->assertNull($request->getCookies()->getSigned('bar'));

		$this->assertFalse($request->getCookies()->getSigned('bar', false));

		$this->assertEquals('bar', $request->getCookies()->getSigned('foo'));

		$this->assertNull($request->getCookies()->getSigned('baz'));

		$this->assertFalse($request->getCookies()->getSigned('baz', false));
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testSignedCookieException()
	{
		$request = new Request();

		$request->getCookies()->getSigned('foo');
	}

	/**
	 *
	 */
	public function testHeader()
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertNull($request->getHeaders()->get('bar'));

		$this->assertFalse($request->getHeaders()->get('bar', false));

		$this->assertEquals('keep-alive', $request->getHeaders()->get('connection'));

		$this->assertEquals('keep-alive', $request->getHeaders()->get('ConNeCtIoN'));
	}

	/**
	 *
	 */
	public function testServer()
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertNull($request->getServer()->get('bar'));

		$this->assertFalse($request->getServer()->get('bar', false));

		$this->assertEquals('example.local', $request->getServer()->get('HTTP_HOST'));

		$this->assertEquals($server, $request->getServer()->all());
	}

	/**
	 *
	 */
	public function testGet()
	{
		$get = ['foo' => 'bar', 'baz' => ['bax']];

		$request = new Request(['get' => $get]);

		$this->assertNull($request->getQuery()->get('bar'));

		$this->assertFalse($request->getQuery()->get('bar', false));

		$this->assertEquals('bar', $request->getQuery()->get('foo'));

		$this->assertEquals('bax', $request->getQuery()->get('baz.0'));

		$this->assertEquals($get, $request->getQuery()->all());
	}

	/**
	 *
	 */
	public function testPost()
	{
		$post = ['foo' => 'bar', 'baz' => ['bax']];

		$request = new Request(['post' => $post]);

		$this->assertNull($request->getPost()->get('bar'));

		$this->assertFalse($request->getPost()->get('bar', false));

		$this->assertEquals('bar', $request->getPost()->get('foo'));

		$this->assertEquals('bax', $request->getPost()->get('baz.0'));

		$this->assertEquals($post, $request->getPost()->all());
	}

	/**
	 *
	 */
	public function testBody()
	{
		$request = new Request(['body' => '{"foo":"bar","baz":["bax"]}']);

		$this->assertEquals('{"foo":"bar","baz":["bax"]}', $request->getRawBody());
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

		$this->assertNull($request->getBody()->get('bar'));

		$this->assertFalse($request->getBody()->get('bar', false));

		$this->assertEquals('bar', $request->getBody()->get('foo'));

		$this->assertEquals('bax', $request->getBody()->get('baz.0'));

		$this->assertEquals($body, $request->getBody()->all());
	}

	/**
	 *
	 */
	public function testJsonPutDataWithCharset()
	{
		$body = ['foo' => 'bar', 'baz' => ['bax']];

		$server = $this->getServerData();

		$server['HTTP_CONTENT_TYPE'] = 'application/json; charset=UTF-8';

		$request = new Request(['server' => $server, 'body' => json_encode($body)]);

		$this->assertNull($request->getBody()->get('bar'));

		$this->assertFalse($request->getBody()->get('bar', false));

		$this->assertEquals('bar', $request->getBody()->get('foo'));

		$this->assertEquals('bax', $request->getBody()->get('baz.0'));

		$this->assertEquals($body, $request->getBody()->all());
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

		$this->assertNull($request->getBody()->get('bar'));

		$this->assertFalse($request->getBody()->get('bar', false));

		$this->assertEquals('bar', $request->getBody()->get('foo'));

		$this->assertEquals('bax', $request->getBody()->get('baz.0'));

		$this->assertEquals($body, $request->getBody()->all());
	}

	/**
	 *
	 */
	public function testSetRoute()
	{
		$request = new Request();

		$route = Mockery::mock('mako\http\routing\Route');

		$request->setRoute($route);
	}

	/**
	 *
	 */
	public function testGetRoute()
	{
		$request = new Request();

		$this->assertNull($request->getRoute());

		$route = Mockery::mock('mako\http\routing\Route');

		$request->setRoute($route);

		$this->assertSame($route, $request->getRoute());
	}

	/**
	 *
	 */
	public function testSetAndGetAttribute()
	{
		$request = new Request();

		$this->assertNull($request->getAttribute('foo'));

		$this->assertFalse($request->getAttribute('foo', false));

		$request->setAttribute('foo', 123);

		$this->assertEquals(123, $request->getAttribute('foo'));

		$this->assertEquals(123, $request->getAttribute('foo', false));
	}

	/**
	 *
	 */
	public function testFile()
	{
		$request =
		[
			'files' =>
			[
				'upload' =>
				[
					'name'     => 'foo',
					'tmp_name' => '/tmp/qwerty',
					'type'     => 'foo/bar',
					'size'     => 123,
					'error'    => 0,
				],
			],
		];

		$request = new Request($request);

		//

		$file = $request->getFiles()->get('upload');

		$this->assertInstanceOf('mako\http\request\UploadedFile', $file);

		$this->assertEquals('foo', $file->getName());

		$this->assertEquals(123, $file->getReportedSize());

		$this->assertEquals('foo/bar', $file->getReportedType());

		$this->assertEquals(0, $file->getErrorCode());
	}

	/**
	 *
	 */
	public function testFileMultiUpload()
	{
		$request =
		[
			'files' =>
			[
				'upload' =>
				[
					'name'     => ['foo', 'bar'],
					'tmp_name' => ['/tmp/qwerty', '/tmp/azerty'],
					'type'     => ['foo/bar', 'foo/bar'],
					'size'     => [123, 456],
					'error'    => [0, 0],
				],
			],
		];

		$request = new Request($request);

		//

		$file = $request->getFiles()->get('upload.0');

		$this->assertInstanceOf('mako\http\request\UploadedFile', $file);

		$this->assertEquals('foo', $file->getName());

		$this->assertEquals(123, $file->getReportedSize());

		$this->assertEquals('foo/bar', $file->getReportedType());

		$this->assertEquals(0, $file->getErrorCode());

		//

		$file = $request->getFiles()->get('upload.1');

		$this->assertInstanceOf('mako\http\request\UploadedFile', $file);

		$this->assertEquals('bar', $file->getName());

		$this->assertEquals(456, $file->getReportedSize());

		$this->assertEquals('foo/bar', $file->getReportedType());

		$this->assertEquals(0, $file->getErrorCode());
	}

	/**
	 *
	 */
	public function testFileNone()
	{
		$request = new Request;

		$this->assertEquals([], $request->getFiles()->all());

		$this->assertNull($request->getFiles()->get('foo'));

		$this->assertFalse($request->getFiles()->get('foo', false));
	}
}
