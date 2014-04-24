<?php

use \mako\http\Request;

class RequestTest extends PHPUnit_Framework_TestCase
{
	/**
	 * 
	 */

	public function getServerData()
	{
		return [
			'HTTP_HOST' => 'example.local',
			'HTTP_CONNECTION' => 'keep-alive',
			'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
			'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36',
			'HTTP_DNT' => '1',
			'HTTP_ACCEPT_ENCODING' => 'gzip,deflate,sdch',
			'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8,da;q=0.6,fr;q=0.4,nb;q=0.2,sv;q=0.2',
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

	public function testIP()
	{
		$server = $this->getServerData();

		$request = new Request(['SERVER' => $server]);

		$this->assertEquals('10.17.12.209', $request->ip());

		//

		$server['REMOTE_ADDR'] = 'invalid ip';

		$request = new Request(['SERVER' => $server]);

		$this->assertEquals('127.0.0.1', $request->ip());

		//

		$server['HTTP_X_FORWARDED_FOR'] = '10.17.12.210,10.17.12.211';

		$request = new Request(['SERVER' => $server]);

		$this->assertEquals('10.17.12.211', $request->ip());

		//

		unset($server['HTTP_X_FORWARDED_FOR']);

		$server['HTTP_CLIENT_IP'] = '2001:0db8:0000:0000:0000:ff00:0042:8329';

		$request = new Request(['SERVER' => $server]);

		$this->assertEquals('2001:0db8:0000:0000:0000:ff00:0042:8329', $request->ip());

		//

		unset($server['HTTP_CLIENT_IP']);

		$server['HTTP_X_CLUSTER_CLIENT_IP'] = '::1';

		$request = new Request(['SERVER' => $server]);

		$this->assertEquals('::1', $request->ip());
	}

	/**
	 * 
	 */

	public function testIsAjax()
	{
		$server = $this->getServerData();

		$request = new Request(['SERVER' => $server]);

		$this->assertFalse($request->isAjax());

		//

		$server['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		$request = new Request(['SERVER' => $server]);

		$this->assertTrue($request->isAjax());
	}

	/**
	 * 
	 */

	public function testIsSecure()
	{
		$server = $this->getServerData();

		$request = new Request(['SERVER' => $server]);

		$this->assertFalse($request->isSecure());

		//

		$server['HTTPS'] = 'off';

		$request = new Request(['SERVER' => $server]);

		$this->assertFalse($request->isSecure());

		//

		$server['HTTPS'] = '0';

		$request = new Request(['SERVER' => $server]);

		$this->assertFalse($request->isSecure());

		//

		$server['HTTPS'] = 'false';

		$request = new Request(['SERVER' => $server]);

		$this->assertFalse($request->isSecure());

		//

		$server['HTTPS'] = 'on';

		$request = new Request(['SERVER' => $server]);

		$this->assertTrue($request->isSecure());

		//

		$server['HTTPS'] = '1';

		$request = new Request(['SERVER' => $server]);

		$this->assertTrue($request->isSecure());

		//

		$server['HTTPS'] = 'true';

		$request = new Request(['SERVER' => $server]);

		$this->assertTrue($request->isSecure());
	}

	/**
	 * 
	 */

	public function testBaseURL()
	{
		$server = $this->getServerData();

		$request = new Request(['SERVER' => $server]);

		$this->assertEquals('http://example.local', $request->baseURL());

		//

		$server['HTTPS'] = 'on';

		$request = new Request(['SERVER' => $server]);

		$this->assertEquals('https://example.local', $request->baseURL());

		//

		$server['SERVER_PORT'] = '8080';

		$request = new Request(['SERVER' => $server]);

		$this->assertEquals('https://example.local:8080', $request->baseURL());
	}

	/**
	 * 
	 */

	public function testPath()
	{
		$server = $this->getServerData();

		$request = new Request(['SERVER' => $server]);

		$this->assertEquals('/test/', $request->path());

		//

		unset($server['PATH_INFO']);

		$request = new Request(['SERVER' => $server]);

		$this->assertEquals('/test/', $request->path());

		//

		$request = new Request(['SERVER' => $server, 'path' => '/foo/bar']);

		$this->assertEquals('/foo/bar', $request->path());
	}

	/**
	 * 
	 */

	public function testPathWithLanguage()
	{
		$server = $this->getServerData();

		$server['PATH_INFO'] = '/no/test/';

		$request = new Request(['SERVER' => $server, 'languages' => ['no' => 'nb_NO']]);

		$this->assertEquals('/test/', $request->path());
	}

	/**
	 * 
	 */

	public function testLanguage()
	{
		$server = $this->getServerData();

		$server['PATH_INFO'] = '/no/test/';

		$request = new Request(['SERVER' => $server, 'languages' => ['no' => 'nb_NO']]);

		$this->assertEquals('nb_NO', $request->language());
	}

	/**
	 * 
	 */

	public function testLanguagePrefix()
	{
		$server = $this->getServerData();

		$server['PATH_INFO'] = '/no/test/';

		$request = new Request(['SERVER' => $server, 'languages' => ['no' => 'nb_NO']]);

		$this->assertEquals('no', $request->languagePrefix());
	}

	/**
	 * 
	 */

	public function testMethod()
	{
		$server = $this->getServerData();

		$request = new Request(['SERVER' => $server]);

		$this->assertEquals('GET', $request->method());

		//

		$request = new Request(['SERVER' => $server, 'method' => 'PATCH']);

		$this->assertEquals('PATCH', $request->method());

		//

		$server['REQUEST_METHOD'] = 'POST';

		$request = new Request(['SERVER' => $server, 'POST' => ['REQUEST_METHOD_OVERRIDE' => 'PUT']]);

		$this->assertEquals('PUT', $request->method());

		//

		$server['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'OPTIONS';

		$request = new Request(['SERVER' => $server]);

		$this->assertEquals('OPTIONS', $request->method());
	}

	/**
	 * 
	 */

	public function testRealMethod()
	{
		$server = $this->getServerData();

		$request = new Request(['SERVER' => $server]);

		$this->assertEquals('GET', $request->realMethod());

		//

		$server['REQUEST_METHOD'] = 'POST';

		$request = new Request(['SERVER' => $server, 'POST' => ['REQUEST_METHOD_OVERRIDE' => 'PUT']]);

		$this->assertEquals('POST', $request->realMethod());

		//

		$server['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'OPTIONS';

		$request = new Request(['SERVER' => $server]);

		$this->assertEquals('POST', $request->realMethod());
	}

	/**
	 * 
	 */

	public function testIsFaked()
	{
		$server = $this->getServerData();

		$request = new Request(['SERVER' => $server]);

		$this->assertFalse($request->isFaked());

		//

		$server['REQUEST_METHOD'] = 'POST';

		$request = new Request(['SERVER' => $server, 'POST' => ['REQUEST_METHOD_OVERRIDE' => 'PUT']]);

		$this->assertTrue($request->isFaked());

		//

		$server['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'OPTIONS';

		$request = new Request(['SERVER' => $server]);

		$this->assertTrue($request->isFaked());
	}

	/**
	 * 
	 */

	public function testUsername()
	{
		$server = $this->getServerData();

		$request = new Request(['SERVER' => $server]);

		$this->assertNull($request->username());

		//

		$server['PHP_AUTH_USER'] = 'foobar';

		$request = new Request(['SERVER' => $server]);

		$this->assertEquals('foobar', $request->username());
	}

	/**
	 * 
	 */

	public function testPassword()
	{
		$server = $this->getServerData();

		$request = new Request(['SERVER' => $server]);

		$this->assertNull($request->password());

		//

		$server['PHP_AUTH_PW'] = 'foobar';

		$request = new Request(['SERVER' => $server]);

		$this->assertEquals('foobar', $request->password());
	}
}