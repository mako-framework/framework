<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\session;

use mako\http\response\Cookies as ResponseCookies;
use mako\session\Session;
use mako\tests\TestCase;
use Mockery;

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
class SessionTest extends TestCase
{
	/**
	 *
	 */
	public function getRequest()
	{
		$request = Mockery::mock('\mako\http\Request');

		$cookies = Mockery::mock('\mako\http\request\Cookies');

		$request->shouldReceive('getCookies')->andReturn($cookies);

		return $request;
	}

	/**
	 *
	 */
	public function getRequestWithCookie()
	{
		$request = $this->getRequest();

		$request->getCookies()->shouldReceive('getSigned')->once()->with('mako_session', false)->andReturn('foo123');

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
	public function getResponseSetCookie($getCookiesTimes = 1, &$responseCookies = null)
	{
		$responseCookies = Mockery::mock(ResponseCookies::class);

		$responseCookies->shouldReceive('addSigned')->once()->with('mako_session', 'foo123', 0, ['path' => '/', 'domain' => '', 'secure' => false, 'httponly' => false]);

		$response = $this->getResponse();

		$response->shouldReceive('getCookies')->times($getCookiesTimes)->andReturn($responseCookies);

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
	public function getDefaultStore($sessionData = [], $commit = false)
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['foo' => 'bar', 'mako.flashdata' => []]);

		if($commit !== false)
		{
			$store->shouldReceive('write')->times()->with('foo123', $sessionData, 1800);
		}

		return $store;
	}

	/**
	 *
	 */
	public function testStartWithCookie()
	{
		new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']), [], false);
	}

	/**
	 *
	 */
	public function testCommit()
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar'], true), [], false);

		$session->commit();
	}

	/**
	 *
	 */
	public function testStartWithoutCookie()
	{
		$request = $this->getRequest();

		$request->getCookies()->shouldReceive('getSigned')->once()->with('mako_session', false)->andReturn(false);

		$responseCookies = Mockery::mock(ResponseCookies::class);

		$responseCookies->shouldReceive('addSigned')->once()->with('mako_session', 'foobar', 0, ['path' => '/', 'domain' => '', 'secure' => false, 'httponly' => false]);

		$response = $this->getResponse();

		$response->shouldReceive('getCookies')->once()->andReturn($responseCookies);

		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foobar')->andReturn([]);

		$store->shouldReceive('write')->once()->with('foobar', ['mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($request, $response, $store, [], false);

		$session->commit();
	}

	/**
	 *
	 */
	public function testGetId()
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']), [], false);

		$this->assertEquals('foo123', $session->getId());
	}

	/**
	 *
	 */
	public function testRegenerateId()
	{
		$responseCookies = Mockery::mock(ResponseCookies::class);

		$responseCookies->shouldReceive('addSigned')->once()->with('mako_session', 'foobar', 0, ['path' => '/', 'domain' => '', 'secure' => false, 'httponly' => false]);

		$response = $this->getResponseSetCookie();

		$response->shouldReceive('getCookies')->once()->andReturn($responseCookies);

		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn([]);

		$store->shouldReceive('delete')->once()->with('foo123');

		$store->shouldReceive('write')->once()->with('foobar', ['mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $response, $store, [], false);

		$session->regenerateId();

		$session->commit();
	}

	/**
	 *
	 */
	public function testRegenerateIdAndKeepData()
	{
		$response = $this->getResponseSetCookie(2, $responseCookies);

		$responseCookies->shouldReceive('addSigned')->once()->with('mako_session', 'foobar', 0, ['path' => '/', 'domain' => '', 'secure' => false, 'httponly' => false]);

		//$response->shouldReceive('signedCookie')->once()->with('mako_session', 'foobar', 0, ['path' => '/', 'domain' => '', 'secure' => false, 'httponly' => false]);

		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn([]);

		$store->shouldReceive('write')->once()->with('foobar', ['mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $response, $store, [], false);

		$session->regenerateId(true);

		$session->commit();
	}

	/**
	 *
	 */
	public function testGetData()
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']), [], false);

		$this->assertEquals(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar'], $session->getData());
	}

	/**
	 *
	 */
	public function testPut()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn([]);

		$store->shouldReceive('write')->once()->with('foo123', ['bax' => 123, 'mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store, [], false);

		$session->put('bax', 123);

		$session->commit();
	}

	/**
	 *
	 */
	public function testHas()
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']), [], false);

		$this->assertTrue($session->has('foo'));

		$this->assertFalse($session->has('bar'));
	}

	/**
	 *
	 */
	public function testGet()
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']), [], false);

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

		$store->shouldReceive('write')->once()->with('foo123', ['mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store, [], false);

		$session->remove('foo');

		$session->commit();
	}

	/**
	 *
	 */
	public function testPutFlash()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn([]);

		$store->shouldReceive('write')->once()->with('foo123', ['mako.flashdata' => ['bax' => 123], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store, [], false);

		$session->putFlash('bax', 123);

		$session->commit();
	}

	/**
	 *
	 */
	public function testHasFlash()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['mako.flashdata' => ['bax' => 123]]);

		$store->shouldReceive('write')->once()->with('foo123', ['mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store, [], false);

		$this->assertTrue($session->hasFlash('bax'));

		$this->assertFalse($session->hasFlash('baz'));

		$session->commit();
	}

	/**
	 *
	 */
	public function testRemoveFlash()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['mako.flashdata' => ['bax' => 123]]);

		$store->shouldReceive('write')->once()->with('foo123', ['mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store, [], false);

		$this->assertTrue($session->hasFlash('bax'));

		$session->removeFlash('bax');

		$this->assertFalse($session->hasFlash('bax'));

		$session->commit();
	}

	/**
	 *
	 */
	public function testReflash()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['mako.flashdata' => ['bax' => 123, 'baz' => 456]]);

		$store->shouldReceive('write')->once()->with('foo123', ['mako.flashdata' => ['bax' => 123, 'baz' => 456], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store, [], false);

		$session->reflash();

		$session->commit();
	}

	/**
	 *
	 */
	public function testReflashWithKeys()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['mako.flashdata' => ['bax' => 123, 'baz' => 456]]);

		$store->shouldReceive('write')->once()->with('foo123', ['mako.flashdata' => ['bax' => 123], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store, [], false);

		$session->reflash(['bax']);

		$session->commit();
	}

	/**
	 *
	 */
	public function testGetToken()
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']), [], false);

		$this->assertSame('foobar', $session->getToken());
	}

	/**
	 *
	 */
	public function testRegenerateToken()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['mako.flashdata' => [], 'mako.token' => 'foobar']);

		$store->shouldReceive('write')->once()->with('foo123', ['mako.flashdata' => [], 'mako.token' => 'bar456'], 1800);

		$session = Mockery::mock('\mako\session\Session[generateId]', [$this->getRequestWithCookie(), $this->getResponseSetCookie(), $store, [], false]);

		$session->shouldAllowMockingProtectedMethods();

		$session->shouldReceive('generateId')->once()->andReturn('bar456');

		$this->assertSame('foobar', $session->getToken());

		$this->assertSame('bar456', $session->regenerateToken());

		$session->commit();
	}

	/**
	 *
	 */
	public function testValidateToken()
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']), [], false);

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

		$store->shouldReceive('write')->once()->with('foo123', ['mako.tokens' => ['foobar'], 'mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store, [], false);

		$token = $session->generateOneTimeToken();

		$this->assertEquals('foobar', $token);

		$session->commit();
	}

	/**
	 *
	 */
	public function testValidateNonExistentOneTimeToken()
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']), [], false);

		$this->assertFalse($session->validateOneTimeToken('bar456'));
	}

	/**
	 *
	 */
	public function testValidateExistingOneTimeToken()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['mako.tokens' => ['bar456'], 'mako.flashdata' => []]);

		$store->shouldReceive('write')->once()->with('foo123', ['mako.tokens' => [], 'mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store, [], false);

		$this->assertTrue($session->validateOneTimeToken('bar456'));

		$session->commit();
	}

	/**
	 *
	 */
	public function testClear()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['foo' => 'bar', 'mako.flashdata' => []]);

		$store->shouldReceive('write')->once()->with('foo123', ['mako.flashdata' => []], 1800);

		$session = new Session($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store, [], false);

		$session->clear();

		$session->commit();
	}

	/**
	 *
	 */
	public function testDestroy()
	{
		$response = $this->getResponseSetCookie(2, $responseCookies);

		$responseCookies->shouldReceive('delete')->once()->with('mako_session', ['path' => '/', 'domain' => '', 'secure' => false, 'httponly' => false]);

		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn([]);

		$store->shouldReceive('delete')->once()->with('foo123');

		$store->shouldReceive('write')->never();

		$session = new Session($this->getRequestWithCookie(), $response, $store, [], false);

		$session->destroy();

		$session->commit();
	}

	/**
	 *
	 */
	public function testGetAndPutExisting()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['foo' => 'bar']);

		$store->shouldReceive('write')->once()->with('foo123', ['foo' => 'baz', 'mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store, [], false);

		$this->assertEquals('bar', $session->getAndPut('foo', 'baz', 'bax'));

		$session->commit();
	}

	/**
	 *
	 */
	public function testGetAndPutNonExisting()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn([]);

		$store->shouldReceive('write')->once()->with('foo123', ['foo' => 'baz', 'mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store, [], false);

		$this->assertEquals('bax', $session->getAndPut('foo', 'baz', 'bax'));

		$session->commit();
	}

	/**
	 *
	 */
	public function testGetAndRemoveExisting()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['foo' => 'bar']);

		$store->shouldReceive('write')->once()->with('foo123', ['mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store, [], false);

		$this->assertEquals('bar', $session->getAndRemove('foo', 'bax'));

		$session->commit();
	}

	/**
	 *
	 */
	public function testGetAndRemoveNonExisting()
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn([]);

		$store->shouldReceive('write')->once()->with('foo123', ['mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store, [], false);

		$this->assertEquals('bax', $session->getAndRemove('foo', 'bax'));

		$session->commit();
	}
}
