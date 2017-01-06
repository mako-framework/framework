<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\session;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\session\Session;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class TestSession extends Session
{
	public function generateId(): string
	{
		return 'foobar';
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class SessionTest extends PHPUnit_Framework_TestCase
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

		$cookies = Mockery::mock('\mako\http\request\Cookies');

		$request->cookies = $cookies;

		return $request;
	}

	/**
	 *
	 */
	public function getRequestWithCookie()
	{
		$request = $this->getRequest();

		$request->cookies->shouldReceive('getSigned')->once()->with('mako_session', false)->andReturn('foo123');

		return $request;
	}

	/**
	 *
	 */
	public function getResponse()
	{
		return Mockery::mock('\mako\http\Response');
	}

	/**
	 *
	 */
	public function getResponseSetCookie()
	{
		$response = $this->getResponse();

		$response->shouldReceive('signedCookie')->once()->with('mako_session', 'foo123', 0, ['path' => '/', 'domain' => '', 'secure' => false, 'httponly' => false]);

		return $response;
	}

	/**
	 *
	 */
	public function getStore()
	{
		$store = Mockery::mock('\mako\session\stores\StoreInterface');

		$store->shouldReceive('gc')->with(1800);

		return $store;
	}

	/**
	 *
	 */
	public function getDefaultStore($sessionData = [])
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['foo' => 'bar', 'mako.flashdata' => []]);

		$store->shouldReceive('write')->once()->with('foo123', $sessionData, 1800);

		return $store;
	}

	/**
	 *
	 */
	public function testStartWithCookie()
	{
		new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']));
	}

	/**
	 *
	 */
	public function testStartWithoutCookie()
	{
		$request = $this->getRequest();

		$request->cookies->shouldReceive('getSigned')->once()->with('mako_session', false)->andReturn(false);

		$response = $this->getResponse();

		$response->shouldReceive('signedCookie')->once()->with('mako_session', 'foobar', 0, ['path' => '/', 'domain' => '', 'secure' => false, 'httponly' => false]);

		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foobar')->andReturn([]);

		$store->shouldReceive('write')/*->once()*/->with('foobar', ['mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		new TestSession($request, $response, $store);
	}

	/**
	 *
	 */
	public function testGetId()
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']));

		$this->assertEquals('foo123', $session->getId());
	}

	/**
	 *
	 */
	public function testRegenerateId()
	{
		$response = $this->getResponseSetCookie();

		$response->shouldReceive('signedCookie')->once()->with('mako_session', 'foobar', 0, ['path' => '/', 'domain' => '', 'secure' => false, 'httponly' => false]);

		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn([]);

		$store->shouldReceive('delete')->once()->with('foo123');

		$store->shouldReceive('write')/*->once()*/->with('foobar', ['mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $response, $store);

		$session->regenerateId();
	}

	/**
	 *
	 */
	public function testRegenerateIdAndKeepData()
	{
		$response = $this->getResponseSetCookie();

		$response->shouldReceive('signedCookie')->once()->with('mako_session', 'foobar', 0, ['path' => '/', 'domain' => '', 'secure' => false, 'httponly' => false]);

		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn([]);

		$store->shouldReceive('write')/*->once()*/->with('foobar', ['mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $response, $store);

		$session->regenerateId(true);
	}

	/**
	 *
	 */
	public function testGetData()
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']));

		$this->assertEquals(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar'], $session->getData());
	}

	/**
	 *
	 */
	public function testPut()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn([]);

		$store->shouldReceive('write')->with('foo123', ['bax' => 123, 'mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store);

		$session->put('bax', 123);
	}

	/**
	 *
	 */
	public function testHas()
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']));

		$this->assertTrue($session->has('foo'));

		$this->assertFalse($session->has('bar'));
	}

	/**
	 *
	 */
	public function testGet()
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']));

		$this->assertEquals('bar', $session->get('foo'));

		$this->assertEquals(null, $session->get('bar'));

		$this->assertEquals(false, $session->get('bar', false));
	}

	/**
	 *
	 */
	public function testRemove()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['foo' => 'bar', 'mako.flashdata' => []]);

		$store->shouldReceive('write')->with('foo123', ['mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store);

		$session->remove('foo');
	}

	/**
	 *
	 */
	public function testPutFlash()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn([]);

		$store->shouldReceive('write')->with('foo123', ['mako.flashdata' => ['bax' => 123], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store);

		$session->putFlash('bax', 123);
	}

	/**
	 *
	 */
	public function testHasFlash()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['mako.flashdata' => ['bax' => 123]]);

		$store->shouldReceive('write')->with('foo123', ['mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store);

		$this->assertTrue($session->hasFlash('bax'));

		$this->assertFalse($session->hasFlash('baz'));
	}

	/**
	 *
	 */
	public function testRemoveFlash()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['mako.flashdata' => ['bax' => 123]]);

		$store->shouldReceive('write')->with('foo123', ['mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store);

		$this->assertTrue($session->hasFlash('bax'));

		$session->removeFlash('bax');

		$this->assertFalse($session->hasFlash('bax'));
	}

	/**
	 *
	 */
	public function testReflash()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['mako.flashdata' => ['bax' => 123, 'baz' => 456]]);

		$store->shouldReceive('write')->with('foo123', ['mako.flashdata' => ['bax' => 123, 'baz' => 456], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store);

		$session->reflash();
	}

	/**
	 *
	 */
	public function testReflashWithKeys()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['mako.flashdata' => ['bax' => 123, 'baz' => 456]]);

		$store->shouldReceive('write')->with('foo123', ['mako.flashdata' => ['bax' => 123], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store);

		$session->reflash(['bax']);
	}

	/**
	 *
	 */
	public function testGetToken()
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']));

		$this->assertSame('foobar', $session->getToken());
	}

	/**
	 *
	 */
	public function testRegenerateToken()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['mako.flashdata' => [], 'mako.token' => 'foobar']);

		$store->shouldReceive('write')/*->once()*/->with('foo123', ['mako.flashdata' => [], 'mako.token' => 'bar456'], 1800);

		$session = Mockery::mock('\mako\session\Session[generateId]', [$this->getRequestWithCookie(), $this->getResponseSetCookie(), $store]);

		$session->shouldAllowMockingProtectedMethods();

		$session->shouldReceive('generateId')->once()->andReturn('bar456');

		$this->assertSame('foobar', $session->getToken());

		$this->assertSame('bar456', $session->regenerateToken());
	}

	/**
	 *
	 */
	public function testValidateToken()
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']));

		$this->assertTrue($session->validateToken('foobar'));

		$this->assertFalse($session->validateToken('barfoo'));
	}

	/**
	 *
	 */
	public function testGenerateOneTimeToken()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn([]);

		$store->shouldReceive('write')->with('foo123', ['mako.flashdata' => []], 1800);

		$store->shouldReceive('write')/*->once()*/->with('foo123', ['mako.tokens' => ['foobar'], 'mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store);

		$token = $session->generateOneTimeToken();

		$this->assertEquals('foobar', $token);
	}

	/**
	 *
	 */
	public function testValidateNonExistentOneTimeToken()
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']));

		$this->assertFalse($session->validateOneTimeToken('bar456'));
	}

	/**
	 *
	 */
	public function testValidateExistingOneTimeToken()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['mako.tokens' => ['bar456'], 'mako.flashdata' => []]);

		$store->shouldReceive('write')->with('foo123', ['mako.tokens' => [], 'mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store);

		$this->assertTrue($session->validateOneTimeToken('bar456'));
	}

	/**
	 *
	 */
	public function testClear()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['foo' => 'bar', 'mako.flashdata' => []]);

		$store->shouldReceive('write')->with('foo123', ['mako.flashdata' => []], 1800);

		$session = new Session($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store);

		$session->clear();
	}

	/**
	 *
	 */
	public function testDestroy()
	{
		$response = $this->getResponseSetCookie();

		$response->shouldReceive('deleteCookie')->once()->with('mako_session', ['path' => '/', 'domain' => '', 'secure' => false, 'httponly' => false]);

		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn([]);

		$store->shouldReceive('delete')->once()->with('foo123');

		$session = new Session($this->getRequestWithCookie(), $response, $store);

		$session->destroy();
	}

	/**
	 *
	 */
	public function testGetAndPutExisting()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['foo' => 'bar']);

		$store->shouldReceive('write')->with('foo123', ['foo' => 'baz', 'mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store);

		$this->assertEquals('bar', $session->getAndPut('foo', 'baz', 'bax'));
	}

	/**
	 *
	 */
	public function testGetAndPutNonExisting()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn([]);

		$store->shouldReceive('write')->with('foo123', ['foo' => 'baz', 'mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store);

		$this->assertEquals('bax', $session->getAndPut('foo', 'baz', 'bax'));
	}

	/**
	 *
	 */
	public function testGetAndRemoveExisting()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['foo' => 'bar']);

		$store->shouldReceive('write')->with('foo123', ['mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store);

		$this->assertEquals('bar', $session->getAndRemove('foo', 'bax'));
	}

	/**
	 *
	 */
	public function testGetAndRemoveNonExisting()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn([]);

		$store->shouldReceive('write')->with('foo123', ['mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store);

		$this->assertEquals('bax', $session->getAndRemove('foo', 'bax'));
	}
}
