<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http;

use mako\http\Request;
use mako\http\request\UploadedFile;
use mako\http\routing\Route;
use mako\security\Signer;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;

#[Group('unit')]
class RequestTest extends TestCase
{
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
	public function testScriptName(): void
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertEquals('index.php', $request->getScriptName());

		//

		$server['SCRIPT_FILENAME'] = '/var/www/app.php';

		$request = new Request(['server' => $server]);

		$this->assertEquals('app.php', $request->getScriptName());

		//

		$server['SCRIPT_FILENAME'] = '/var/www/reactor';

		$request = new Request(['server' => $server]);

		$this->assertEquals('index.php', $request->getScriptName());
	}

	/**
	 *
	 */
	public function testScriptNameOverride(): void
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server], null, 'foo.php');

		$this->assertEquals('foo.php', $request->getScriptName());
	}

	/**
	 *
	 */
	public function testAcceptableContentTypes(): void
	{
		$server = $this->getServerData();

		$acceptableContentTypes = ['text/html', 'application/xhtml+xml', 'image/webp', 'application/xml', '*/*', 'foo/bar'];

		$this->assertEquals($acceptableContentTypes, (new Request(['server' => $server]))->getHeaders()->getAcceptableContentTypes());
	}

	/**
	 *
	 */
	public function testAcceptableLanguages(): void
	{
		$server = $this->getServerData();

		$acceptableLanguages = ['en-US', 'en', 'da', 'fr', 'nb', 'sv', 'foo'];

		$this->assertEquals($acceptableLanguages, (new Request(['server' => $server]))->getHeaders()->getAcceptableLanguages());
	}

	/**
	 *
	 */
	public function testAcceptableCharsets(): void
	{
		$server = $this->getServerData();

		$acceptableCharsets = ['UTF-8', 'UTF-16', 'FOO-1'];

		$this->assertEquals($acceptableCharsets, (new Request(['server' => $server]))->getHeaders()->getAcceptableCharsets());
	}

	/**
	 *
	 */
	public function testAcceptableEncodings(): void
	{
		$server = $this->getServerData();

		$acceptableEncodings = ['gzip', 'deflate', 'sdch', 'foobar'];

		$this->assertEquals($acceptableEncodings, (new Request(['server' => $server]))->getHeaders()->getAcceptableEncodings());
	}

	/**
	 *
	 */
	public function testIP(): void
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertEquals('10.17.12.209', $request->getIp());

		// Should fall back to localhost if an invalid IP is detected

		$server['REMOTE_ADDR'] = 'invalid ip';

		$request = new Request(['server' => $server]);

		$this->assertEquals(Request::REMOTE_ADDRESS_FALLBACK, $request->getIp());

		// Should ignore the X-Forwarded-For header if no list of trusted proxies is specified

		$server['REMOTE_ADDR'] = '10.17.12.214';

		$server['HTTP_X_FORWARDED_FOR'] = '10.17.13.0, 10.17.13.1, 10.17.12.212, 10.17.12.213';

		$request = new Request(['server' => $server]);

		$this->assertEquals('10.17.12.214', $request->getIp());

		// Should return the last IP in the chain since it doesn't match our trusted proxy

		$request = new Request(['server' => $server]);

		$request->setTrustedProxies(['10.17.12.214']);

		$this->assertEquals('10.17.12.213', $request->getIp());

		// Should return the IP forwarded by the first trusted proxy

		$request = new Request(['server' => $server]);

		$request->setTrustedProxies(['10.17.12.213', '10.17.12.214']);

		$this->assertEquals('10.17.12.212', $request->getIp());

		// Should return the IP forwarded by the first trusted proxy

		$request = new Request(['server' => $server]);

		$request->setTrustedProxies(['10.17.12.212', '10.17.12.213', '10.17.12.214']);

		$this->assertEquals('10.17.13.1', $request->getIp());

		// Should return the IP forwarded by the first trusted proxy

		$request = new Request(['server' => $server]);

		$request->setTrustedProxies(['10.17.12.0/24']);

		$this->assertEquals('10.17.13.1', $request->getIp());
	}

	/**
	 *
	 */
	public function testGetIpWithoutRemoteAddress(): void
	{
		$server = $this->getServerData();

		unset($server['REMOTE_ADDR']);

		$request = new Request(['server' => $server]);

		$this->assertSame(Request::REMOTE_ADDRESS_FALLBACK, $request->getIp());
	}

	/**
	 *
	 */
	public function testIsAjax(): void
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
	public function testIsSecure(): void
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

		//

		$server['HTTPS'] = 'false';
		$server['HTTP_X_FORWARDED_PROTO'] = 'https';
		$server['REMOTE_ADDR'] = '127.0.0.1';

		$request = new Request(['server' => $server]);

		$request->setTrustedProxies(['127.0.0.1']);

		$this->assertTrue($request->isSecure());
	}

	/**
	 *
	 */
	public function testIsSecureWithoutRemoteAddress(): void
	{
		$server = $this->getServerData();

		unset($server['REMOTE_ADDR']);

		$request = new Request(['server' => $server]);

		$this->assertFalse($request->isSecure());
	}

	/**
	 *
	 */
	public function testBasePath(): void
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertEquals('', $request->getBasePath());

		//

		$server = $this->getServerData();

		$server['SCRIPT_NAME'] = '/foo/bar/index.php';

		$request = new Request(['server' => $server]);

		$this->assertEquals('/foo/bar', $request->getBasePath());
	}

	/**
	 *
	 */
	public function testBaseURL(): void
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertEquals('http://example.local', $request->getBaseURL());

		//

		$server = $this->getServerData();

		$server['HTTPS'] = 'on';

		$request = new Request(['server' => $server]);

		$this->assertEquals('https://example.local', $request->getBaseURL());

		//

		$server = $this->getServerData();

		$server['HTTPS'] = 'on';

		$server['HTTP_HOST'] = 'example.local:8080';

		$request = new Request(['server' => $server]);

		$this->assertEquals('https://example.local:8080', $request->getBaseURL());

		//

		$server = $this->getServerData();

		$server['HTTPS'] = 'on';

		unset($server['HTTP_HOST']);

		$request = new Request(['server' => $server]);

		$this->assertEquals('https://example.local', $request->getBaseURL());

		//

		$server = $this->getServerData();

		$server['HTTPS'] = 'on';

		unset($server['HTTP_HOST']);

		$server['SERVER_PORT'] = '8080';

		$request = new Request(['server' => $server]);

		$this->assertEquals('https://example.local:8080', $request->getBaseURL());

		//

		$server = $this->getServerData();

		$server['SCRIPT_NAME'] = '/foo/bar/index.php';

		$request = new Request(['server' => $server]);

		$this->assertEquals('http://example.local/foo/bar', $request->getBaseURL());

		//

		$server = $this->getServerData();

		$server['HTTPS'] = 'off';
		$server['HTTP_X_FORWARDED_PROTO'] = 'https';
		$server['REMOTE_ADDR'] = '127.0.0.1';

		$request = new Request(['server' => $server]);

		$request->setTrustedProxies(['127.0.0.1']);

		$this->assertEquals('https://example.local', $request->getBaseURL());
	}

	/**
	 *
	 */
	public function testPath(): void
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertEquals('/test/', $request->getPath());

		//

		unset($server['PATH_INFO']);

		$request = new Request(['server' => $server]);

		$this->assertEquals('/test/', $request->getPath());

		//

		$server['REQUEST_URI'] = '/app.php/test/';
		$server['SCRIPT_NAME'] = '/app.php';
		$server['SCRIPT_FILENAME'] = '/var/www/app.php';

		$request = new Request(['server' => $server]);

		$this->assertEquals('/test/', $request->getPath());

		//

		$request = new Request(['server' => $server, 'path' => '/foo/bar']);

		$this->assertEquals('/foo/bar', $request->getPath());
	}

	/**
	 *
	 */
	public function testPathWithLanguage(): void
	{
		$server = $this->getServerData();

		$server['PATH_INFO'] = '/no/test/';

		$request = new Request(['server' => $server, 'languages' => ['no' => ['strings' => 'nb_NO', 'locale' => [LC_ALL => ['nb_NO.UTF-8', 'nb_NO.utf8', 'C'], LC_NUMERIC => 'C']]]]);

		$this->assertEquals('/test/', $request->getPath());
	}

	/**
	 *
	 */
	public function testIsClean(): void
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
	public function testLanguage(): void
	{
		$server = $this->getServerData();

		$server['PATH_INFO'] = '/no/test/';

		$request = new Request(['server' => $server, 'languages' => ['no' => ['strings' => 'nb_NO', 'locale' => [LC_ALL => ['nb_NO.UTF-8', 'nb_NO.utf8', 'C'], LC_NUMERIC => 'C']]]]);

		$this->assertEquals(['strings' => 'nb_NO', 'locale' => [LC_ALL => ['nb_NO.UTF-8', 'nb_NO.utf8', 'C'], LC_NUMERIC => 'C']], $request->getLanguage());
	}

	/**
	 *
	 */
	public function testLanguagePrefix(): void
	{
		$server = $this->getServerData();

		$server['PATH_INFO'] = '/no/test/';

		$request = new Request(['server' => $server, 'languages' => ['no' => ['strings' => 'nb_NO', 'locale' => [LC_ALL => ['nb_NO.UTF-8', 'nb_NO.utf8', 'C'], LC_NUMERIC => 'C']]]]);

		$this->assertEquals('no', $request->getLanguagePrefix());
	}

	/**
	 *
	 */
	public function testMethod(): void
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertEquals('GET', $request->getMethod());

		//

		$request = new Request(['server' => $server, 'method' => 'PATCH']);

		$this->assertEquals('PATCH', $request->getMethod());

		//

		$server['REQUEST_METHOD'] = 'POST';

		$request = new Request(['server' => $server, 'post' => ['REQUEST_METHOD_OVERRIDE' => 'PUT']]);

		$this->assertEquals('PUT', $request->getMethod());

		//

		$server['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'OPTIONS';

		$request = new Request(['server' => $server]);

		$this->assertEquals('OPTIONS', $request->getMethod());
	}

	/**
	 *
	 */
	public function testRealMethod(): void
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertEquals('GET', $request->getRealMethod());

		//

		$server['REQUEST_METHOD'] = 'POST';

		$request = new Request(['server' => $server, 'post' => ['REQUEST_METHOD_OVERRIDE' => 'PUT']]);

		$this->assertEquals('POST', $request->getRealMethod());

		//

		$server['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'OPTIONS';

		$request = new Request(['server' => $server]);

		$this->assertEquals('POST', $request->getRealMethod());
	}

	/**
	 *
	 */
	public function testIsFaked(): void
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
	public function testUsername(): void
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertNull($request->getUsername());

		//

		$server['PHP_AUTH_USER'] = 'foobar';

		$request = new Request(['server' => $server]);

		$this->assertEquals('foobar', $request->getUsername());
	}

	/**
	 *
	 */
	public function testPassword(): void
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertNull($request->getPassword());

		//

		$server['PHP_AUTH_PW'] = 'foobar';

		$request = new Request(['server' => $server]);

		$this->assertEquals('foobar', $request->getPassword());
	}

	/**
	 *
	 */
	public function testCookie(): void
	{
		$cookies = ['foo' => 'bar'];

		$request = new Request(['cookies' => $cookies]);

		$this->assertNull($request->getCookies()->get('bar'));

		$this->assertFalse($request->getCookies()->get('bar', false));

		$this->assertEquals('bar', $request->getCookies()->get('foo'));

		$this->assertEquals('bar', $request->cookies->get('foo'));

		$request->cookies->add('baz', 'bax');

		$this->assertEquals('bax', $request->cookies->get('baz'));
	}

	/**
	 *
	 */
	public function testSignedCookie(): void
	{
		$signer = Mockery::mock(Signer::class);

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
	 *
	 */
	public function testSignedCookieException(): void
	{
		$this->expectException(RuntimeException::class);

		$request = new Request;

		$request->getCookies()->getSigned('foo');
	}

	/**
	 *
	 */
	public function testHeader(): void
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertNull($request->getHeaders()->get('bar'));

		$this->assertFalse($request->getHeaders()->get('bar', false));

		$this->assertEquals('keep-alive', $request->getHeaders()->get('connection'));

		$this->assertEquals('keep-alive', $request->getHeaders()->get('ConNeCtIoN'));

		$this->assertEquals('keep-alive', $request->headers->get('connection'));

		$request->headers->add('oof', 'rab');

		$this->assertEquals('rab', $request->headers->get('oof'));
	}

	/**
	 *
	 */
	public function testServer(): void
	{
		$server = $this->getServerData();

		$request = new Request(['server' => $server]);

		$this->assertNull($request->getServer()->get('bar'));

		$this->assertFalse($request->getServer()->get('bar', false));

		$this->assertEquals('example.local', $request->getServer()->get('HTTP_HOST'));

		$this->assertEquals($server, $request->getServer()->all());

		$this->assertEquals('example.local', $request->server->get('HTTP_HOST'));

		$request->server->add('oof', 'rab');

		$this->assertEquals('rab', $request->server->get('oof'));
	}

	/**
	 *
	 */
	public function testGet(): void
	{
		$get = ['foo' => 'bar', 'baz' => ['bax']];

		$request = new Request(['get' => $get]);

		$this->assertNull($request->getQuery()->get('bar'));

		$this->assertFalse($request->getQuery()->get('bar', false));

		$this->assertEquals('bar', $request->getQuery()->get('foo'));

		$this->assertEquals('bax', $request->getQuery()->get('baz.0'));

		$this->assertEquals($get, $request->getQuery()->all());

		$this->assertEquals('bar', $request->query->get('foo'));

		$request->query->add('oof', 'rab');

		$this->assertEquals('rab', $request->query->get('oof'));
	}

	/**
	 *
	 */
	public function testPost(): void
	{
		$post = ['foo' => 'bar', 'baz' => ['bax']];

		$request = new Request(['post' => $post]);

		$this->assertNull($request->getPost()->get('bar'));

		$this->assertFalse($request->getPost()->get('bar', false));

		$this->assertEquals('bar', $request->getPost()->get('foo'));

		$this->assertEquals('bax', $request->getPost()->get('baz.0'));

		$this->assertEquals($post, $request->getPost()->all());

		$this->assertEquals('bar', $request->post->get('foo'));

		$request->post->add('oof', 'rab');

		$this->assertEquals('rab', $request->getPost()->get('oof'));
	}

	/**
	 *
	 */
	public function testBody(): void
	{
		$request = new Request(['body' => '{"foo":"bar","baz":["bax"]}']);

		$this->assertEquals('{"foo":"bar","baz":["bax"]}', $request->getRawBody());
	}

	/**
	 *
	 */
	public function testContentTypeWithNoHeader(): void
	{
		$request = new Request;

		$this->assertSame('', $request->getContentType());
	}

	/**
	 *
	 */
	public function testContentTypeWithHeader(): void
	{
		$server = $this->getServerData();

		$server['HTTP_CONTENT_TYPE'] = 'application/json';

		$request = new Request(['server' => $server]);

		$this->assertSame('application/json', $request->getContentType());
	}

	/**
	 *
	 */
	public function testContentTypeWithHeaderAndCharset(): void
	{
		$server = $this->getServerData();

		$server['HTTP_CONTENT_TYPE'] = 'application/json; charset=UTF-8';

		$request = new Request(['server' => $server]);

		$this->assertSame('application/json', $request->getContentType());
	}

	/**
	 *
	 */
	public function testJsonPutData(): void
	{
		$body = ['foo' => 'bar', 'baz' => ['bax']];

		$server = $this->getServerData();

		$server['HTTP_CONTENT_TYPE'] = 'application/json';

		$request = new Request(['server' => $server, 'body' => json_encode($body)]);

		$this->assertNull($request->body->get('bar'));

		$this->assertFalse($request->body->get('bar', false));

		$this->assertEquals('bar', $request->body->get('foo'));

		$this->assertEquals('bax', $request->body->get('baz.0'));

		$this->assertEquals($body, $request->body->all());

		//

		$this->assertNull($request->getBody()->get('bar'));

		$this->assertFalse($request->getBody()->get('bar', false));

		$this->assertEquals('bar', $request->getBody()->get('foo'));

		$this->assertEquals('bax', $request->getBody()->get('baz.0'));

		$this->assertEquals($body, $request->getBody()->all());
	}

	/**
	 *
	 */
	public function testURLEncodedPutData(): void
	{
		$body = ['foo' => 'bar', 'baz' => ['bax']];

		$server = $this->getServerData();

		$server['HTTP_CONTENT_TYPE'] = 'application/x-www-form-urlencoded';

		$request = new Request(['server' => $server, 'body' => http_build_query($body)]);

		$this->assertNull($request->body->get('bar'));

		$this->assertFalse($request->body->get('bar', false));

		$this->assertEquals('bar', $request->body->get('foo'));

		$this->assertEquals('bax', $request->body->get('baz.0'));

		$this->assertEquals($body, $request->body->all());

		//

		$this->assertNull($request->getBody()->get('bar'));

		$this->assertFalse($request->getBody()->get('bar', false));

		$this->assertEquals('bar', $request->getBody()->get('foo'));

		$this->assertEquals('bax', $request->getBody()->get('baz.0'));

		$this->assertEquals($body, $request->getBody()->all());
	}

	/**
	 *
	 */
	public function testSetRoute(): void
	{
		$request = new Request;

		$route = Mockery::mock(Route::class);

		$request->setRoute($route);

		$route = (function () {
			return $this->route;
		})->bindTo($request, Request::class)();

		$this->assertInstanceOf(Route::class, $route);
	}

	/**
	 *
	 */
	public function testGetRoute(): void
	{
		$request = new Request;

		$this->assertNull($request->getRoute());

		$route = Mockery::mock(Route::class);

		$request->setRoute($route);

		$this->assertSame($route, $request->getRoute());
	}

	/**
	 *
	 */
	public function testSetAndGetAttribute(): void
	{
		$request = new Request;

		$this->assertNull($request->getAttribute('foo'));

		$this->assertFalse($request->getAttribute('foo', false));

		$request->setAttribute('foo', 123);

		$this->assertEquals(123, $request->getAttribute('foo'));

		$this->assertEquals(123, $request->getAttribute('foo', false));
	}

	/**
	 *
	 */
	public function testFile(): void
	{
		$request =
		[
			'files' => [
				'upload' => [
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

		$this->assertInstanceOf(UploadedFile::class, $file);

		$this->assertEquals('foo', $file->getReportedFilename());

		$this->assertEquals(123, $file->getReportedSize());

		$this->assertEquals('foo/bar', $file->getReportedMimeType());

		$this->assertEquals(0, $file->getErrorCode());

		//

		$file = $request->files->get('upload');

		$this->assertInstanceOf(UploadedFile::class, $file);

		//

		$request->files->add('foo', $file);

		$file = $request->files->get('foo');

		$this->assertInstanceOf(UploadedFile::class, $file);
	}

	/**
	 *
	 */
	public function testFileMultiUpload(): void
	{
		$request =
		[
			'files' => [
				'upload' => [
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

		$this->assertInstanceOf(UploadedFile::class, $file);

		$this->assertEquals('foo', $file->getReportedFilename());

		$this->assertEquals(123, $file->getReportedSize());

		$this->assertEquals('foo/bar', $file->getReportedMimeType());

		$this->assertEquals(0, $file->getErrorCode());

		//

		$file = $request->getFiles()->get('upload.1');

		$this->assertInstanceOf(UploadedFile::class, $file);

		$this->assertEquals('bar', $file->getReportedFilename());

		$this->assertEquals(456, $file->getReportedSize());

		$this->assertEquals('foo/bar', $file->getReportedMimeType());

		$this->assertEquals(0, $file->getErrorCode());
	}

	/**
	 *
	 */
	public function testFileNone(): void
	{
		$request = new Request;

		$this->assertEquals([], $request->getFiles()->all());

		$this->assertNull($request->getFiles()->get('foo'));

		$this->assertFalse($request->getFiles()->get('foo', false));
	}

	/**
	 *
	 */
	public function testGetDataWithGetRequest(): void
	{
		$get = ['foo' => 'bar', 'baz' => ['bax']];

		$request = new Request(['get' => $get]);

		$this->assertSame($request->data->all(), $request->getQuery()->all());

		$this->assertSame($request->getData()->all(), $request->getQuery()->all());
	}

	/**
	 *
	 */
	public function testGetDataWithPostRequestWithFormData(): void
	{
		$post = ['foo' => 'bar', 'baz' => ['bax']];

		//

		$server = ['REQUEST_METHOD' => 'POST', 'CONTENT_TYPE' => 'application/x-www-form-urlencoded'] + $this->getServerData();

		$request = new Request(['post' => $post, 'server' => $server]);

		$this->assertSame($request->data->all(), $request->getPost()->all());

		$this->assertSame($request->getData()->all(), $request->getPost()->all());

		//

		$server = ['REQUEST_METHOD' => 'POST', 'CONTENT_TYPE' => 'multipart/form-data'] + $this->getServerData();

		$request = new Request(['post' => $post, 'server' => $server]);

		$this->assertSame($request->data->all(), $request->getPost()->all());

		$this->assertSame($request->getData()->all(), $request->getPost()->all());
	}

	/**
	 *
	 */
	public function testGetDataWithPostRequestWithoutFormData(): void
	{
		$body = ['foo' => 'bar', 'baz' => ['bax']];

		//

		$server = ['REQUEST_METHOD' => 'POST', 'CONTENT_TYPE' => 'application/json'] + $this->getServerData();

		$request = new Request(['body' => json_encode($body), 'server' => $server]);

		$this->assertSame($request->data->all(), $request->body->all());

		$this->assertSame($request->getData()->all(), $request->body->all());

		//

		$this->assertSame($request->data->all(), $request->getBody()->all());

		$this->assertSame($request->getData()->all(), $request->getBody()->all());
	}

	/**
	 *
	 */
	public function testIsSafe(): void
	{
		$methods =
		[
			'CONNECT' => false,
			'DELETE'  => false,
			'GET'     => true,
			'HEAD'    => true,
			'OPTIONS' => true,
			'PATCH'   => false,
			'POST'    => false,
			'PUT'     => false,
			'TRACE'   => true,
		];

		foreach ($methods as $method => $isSafe) {
			$request = new Request(['server' => ['REQUEST_METHOD' => $method]]);

			$this->assertSame($isSafe, $request->isSafe());
		}
	}

	/**
	 *
	 */
	public function testIsIdempotent(): void
	{
		$methods =
		[
			'CONNECT' => false,
			'DELETE'  => true,
			'GET'     => true,
			'HEAD'    => true,
			'OPTIONS' => true,
			'PATCH'   => false,
			'POST'    => false,
			'PUT'     => true,
			'TRACE'   => true,
		];

		foreach ($methods as $method => $isIdempotent) {
			$request = new Request(['server' => ['REQUEST_METHOD' => $method]]);

			$this->assertSame($isIdempotent, $request->isIdempotent());
		}
	}

	/**
	 *
	 */
	public function testIsCacheable(): void
	{
		$methods =
		[
			'CONNECT' => false,
			'DELETE'  => false,
			'GET'     => true,
			'HEAD'    => true,
			'OPTIONS' => false,
			'PATCH'   => false,
			'POST'    => false,
			'PUT'     => false,
			'TRACE'   => false,
		];

		foreach ($methods as $method => $isCacheable) {
			$request = new Request(['server' => ['REQUEST_METHOD' => $method]]);

			$this->assertSame($isCacheable, $request->isCacheable());
		}
	}
}
