<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\session;

use mako\http\Request;
use mako\http\request\Cookies as RequestCookies;
use mako\http\Response;
use mako\http\response\Cookies as ResponseCookies;
use mako\session\exceptions\SessionException;
use mako\session\Session;
use mako\session\stores\StoreInterface;
use mako\tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;

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

#[Group('unit')]
class SessionTest extends TestCase
{
	/**
	 *
	 */
	protected function getRequest(): MockInterface&Request
	{
		$request = Mockery::mock(Request::class);

		$cookies = Mockery::mock(RequestCookies::class);

		(function () use ($cookies): void {
			$this->cookies = $cookies;
		})->bindTo($request, Request::class)();

		return $request;
	}

	/**
	 *
	 */
	protected function getRequestWithCookie(): MockInterface&Request
	{
		$request = $this->getRequest();

		$request->cookies->shouldReceive('getSigned')->once()->with('mako_session', false)->andReturn('foo123');

		return $request;
	}

	/**
	 *
	 */
	protected function getResponse(): MockInterface&Response
	{
		return Mockery::mock(Response::class);
	}

	/**
	 *
	 */
	protected function getResponseSetCookie(&$responseCookies = null): MockInterface&Response
	{
		if ($responseCookies === null) {
			$responseCookies = Mockery::mock(ResponseCookies::class);
		}

		$responseCookies->shouldReceive('addSigned')->once()->with('mako_session', 'foo123', 0, ['path' => '/', 'domain' => '', 'secure' => false, 'httponly' => true]);

		$response = $this->getResponse();

		(function () use ($responseCookies): void {
			$this->cookies = $responseCookies;
		})->bindTo($response, Response::class)();

		return $response;
	}

	/**
	 *
	 */
	protected function getStore(): MockInterface&StoreInterface
	{
		$store = Mockery::mock(StoreInterface::class);

		$store->shouldReceive('gc')->with(1800);

		return $store;
	}

	/**
	 *
	 */
	protected function getDefaultStore($sessionData = [], $commit = false): MockInterface&StoreInterface
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['foo' => 'bar', 'mako.flashdata' => []]);

		if ($commit !== false) {
			$store->shouldReceive('write')->times()->with('foo123', $sessionData, 1800);
		}

		return $store;
	}

	/**
	 *
	 */
	public function testStartWithCookie(): void
	{
		new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']), [], false);
	}

	/**
	 *
	 */
	public function testStartWithSecureCookieOverNonSecureConnection(): void
	{
		$this->expectException(SessionException::class);

		$this->expectExceptionMessage('Attempted to set a secure cookie over a non-secure connection.');

		$request = $this->getRequestWithCookie();

		$request->shouldReceive('isSecure')->once()->andReturn(false);

		new TestSession($request, $this->getResponse(), $this->getStore(), ['cookie_options' => ['secure' => true]], false);
	}

	/**
	 *
	 */
	public function testStartWithSecureCookieOverSecureConnection(): void
	{
		$request = $this->getRequestWithCookie();

		$request->shouldReceive('isSecure')->once()->andReturn(true);

		$responseCookies = Mockery::mock(ResponseCookies::class);

		$responseCookies->shouldReceive('addSigned')->once()->with('mako_session', 'foo123', 0, ['path' => '/', 'domain' => '', 'secure' => true, 'httponly' => true]);

		$response = $this->getResponse();

		(function () use ($responseCookies): void {
			$this->cookies = $responseCookies;
		})->bindTo($response, Response::class)();

		new TestSession($request, $response, $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']), ['cookie_options' => ['secure' => true]], false);
	}

	/**
	 *
	 */
	public function testCommit(): void
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar'], true), [], false);

		$session->commit();
	}

	/**
	 *
	 */
	public function testStartWithoutCookie(): void
	{
		$request = $this->getRequest();

		$request->cookies->shouldReceive('getSigned')->once()->with('mako_session', false)->andReturn(false);

		$responseCookies = Mockery::mock(ResponseCookies::class);

		$responseCookies->shouldReceive('addSigned')->once()->with('mako_session', 'foobar', 0, ['path' => '/', 'domain' => '', 'secure' => false, 'httponly' => true]);

		$response = $this->getResponse();

		(function () use ($responseCookies): void {
			$this->cookies = $responseCookies;
		})->bindTo($response, Response::class)();

		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foobar')->andReturn([]);

		$store->shouldReceive('write')->once()->with('foobar', ['mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($request, $response, $store, [], false);

		$session->commit();
	}

	/**
	 *
	 */
	public function testGetId(): void
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']), [], false);

		$this->assertEquals('foo123', $session->getId());
	}

	/**
	 *
	 */
	public function testRegenerateId(): void
	{
		$responseCookies = Mockery::mock(ResponseCookies::class);

		$responseCookies->shouldReceive('addSigned')->once()->with('mako_session', 'foobar', 0, ['path' => '/', 'domain' => '', 'secure' => false, 'httponly' => true]);

		$response = $this->getResponseSetCookie($responseCookies);

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
	public function testRegenerateIdAndKeepData(): void
	{
		$response = $this->getResponseSetCookie($responseCookies);

		$responseCookies->shouldReceive('addSigned')->once()->with('mako_session', 'foobar', 0, ['path' => '/', 'domain' => '', 'secure' => false, 'httponly' => true]);

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
	public function testGetData(): void
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']), [], false);

		$this->assertEquals(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar'], $session->getData());
	}

	/**
	 *
	 */
	public function testPut(): void
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
	public function testHas(): void
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']), [], false);

		$this->assertTrue($session->has('foo'));

		$this->assertFalse($session->has('bar'));
	}

	/**
	 *
	 */
	public function testGet(): void
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']), [], false);

		$this->assertEquals('bar', $session->get('foo'));

		$this->assertEquals(null, $session->get('bar'));

		$this->assertEquals(false, $session->get('bar', false));
	}

	/**
	 *
	 */
	public function testRemove(): void
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
	public function testPutFlash(): void
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
	public function testHasFlash(): void
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
	public function testRemoveFlash(): void
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
	public function testReflash(): void
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
	public function testReflashWithKeys(): void
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
	public function testGetToken(): void
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']), [], false);

		$this->assertSame('foobar', $session->getToken());
	}

	/**
	 *
	 */
	public function testRegenerateToken(): void
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn(['mako.flashdata' => [], 'mako.token' => 'foobar']);

		$store->shouldReceive('write')->once()->with('foo123', ['mako.flashdata' => [], 'mako.token' => 'bar456'], 1800);

		$session = Mockery::mock(Session::class . '[generateId]', [$this->getRequestWithCookie(), $this->getResponseSetCookie(), $store, [], false]);

		$session->shouldAllowMockingProtectedMethods();

		$session->shouldReceive('generateId')->once()->andReturn('bar456');

		$this->assertSame('foobar', $session->getToken());

		$this->assertSame('bar456', $session->regenerateToken());

		$session->commit();
	}

	/**
	 *
	 */
	public function testValidateToken(): void
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']), [], false);

		$this->assertTrue($session->validateToken('foobar'));

		$this->assertFalse($session->validateToken('barfoo'));
	}

	/**
	 *
	 */
	public function testGenerateOneTimeToken(): void
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
	public function testValidateNonExistentOneTimeToken(): void
	{
		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $this->getDefaultStore(['foo' => 'bar', 'mako.flashdata' => [], 'mako.token' => 'foobar']), [], false);

		$this->assertFalse($session->validateOneTimeToken('bar456'));
	}

	/**
	 *
	 */
	public function testValidateExistingOneTimeToken(): void
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
	public function testClear(): void
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
	public function testDestroy(): void
	{
		$response = $this->getResponseSetCookie($responseCookies);

		$responseCookies->shouldReceive('delete')->once()->with('mako_session', ['path' => '/', 'domain' => '', 'secure' => false, 'httponly' => true]);

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
	public function testGetAndPutExisting(): void
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
	public function testGetAndPutNonExisting(): void
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
	public function testGetAndRemoveExisting(): void
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
	public function testGetAndRemoveNonExisting(): void
	{
		$store = $this->getStore();

		$store->shouldReceive('read')->once()->with('foo123')->andReturn([]);

		$store->shouldReceive('write')->once()->with('foo123', ['mako.flashdata' => [], 'mako.token' => 'foobar'], 1800);

		$session = new TestSession($this->getRequestWithCookie(), $this->getResponseSetCookie(), $store, [], false);

		$this->assertEquals('bax', $session->getAndRemove('foo', 'bax'));

		$session->commit();
	}
}
