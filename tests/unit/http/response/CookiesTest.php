<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http;

use mako\http\response\Cookies;
use mako\security\Signer;
use mako\tests\TestCase;
use Mockery;
use RuntimeException;

/**
 * @group unit
 */
class CookiesTest extends TestCase
{
	/**
	 *
	 */
	public function testCountable(): void
	{
		$cookies = new Cookies;

		$this->assertSame(0, count($cookies));

		$cookies->add('foo', 'bar');

		$this->assertSame(1, count($cookies));
	}

	/**
	 *
	 */
	public function testIterable(): void
	{
		$cookies = new Cookies;

		$cookies->add('foo', 'bar');

		foreach($cookies as $cookie)
		{
			$this->assertTrue(is_array($cookie));

			$this->assertArrayHasKey('raw', $cookie);
			$this->assertArrayHasKey('name', $cookie);
			$this->assertArrayHasKey('value', $cookie);
			$this->assertArrayHasKey('expires', $cookie['options']);
			$this->assertArrayHasKey('path', $cookie['options']);
			$this->assertArrayHasKey('domain', $cookie['options']);
			$this->assertArrayHasKey('secure', $cookie['options']);
			$this->assertArrayHasKey('httponly', $cookie['options']);
		}
	}

	/**
	 *
	 */
	public function testAddSigned(): void
	{
		$signer = Mockery::mock(Signer::class);

		$signer->shouldReceive('sign')->once()->with('value')->andReturn('signedvalue');

		$cookies = new Cookies($signer);

		$cookies->addSigned('foo', 'value');

		$this->assertSame('signedvalue', $cookies->all()['foo']['value']);
	}

	/**
	 *
	 */
	public function testAddSignedWithoutSigner(): void
	{
		$this->expectException(RuntimeException::class);

		$this->expectExceptionMessage('A [ Signer ] instance is required to sign cookies.');

		$cookies = new Cookies;

		$cookies->addSigned('foo', 'value');
	}

	/**
	 *
	 */
	public function testHas(): void
	{
		$cookies = new Cookies;

		$this->assertFalse($cookies->has('foo'));

		$cookies->add('foo', 'bar');

		$this->assertTrue($cookies->has('foo'));
	}

	/**
	 *
	 */
	public function testRemove(): void
	{
		$cookies = new Cookies;

		$cookies->add('foo', 'bar');

		$this->assertTrue($cookies->has('foo'));

		$cookies->remove('foo');

		$this->assertFalse($cookies->has('foo'));
	}

	/**
	 *
	 */
	public function testDelete(): void
	{
		$cookies = new Cookies;

		$cookies->delete('foo');

		$this->assertTrue($cookies->all()['foo']['options']['expires'] + 100 < time());
	}

	/**
	 *
	 */
	public function testClear(): void
	{
		$cookies = new Cookies;

		$cookies->add('foo', 'bar');

		$this->assertSame(1, count($cookies));

		$cookies->clear();

		$this->assertSame(0, count($cookies));
	}

	/**
	 *
	 */
	public function testAddWithOptions(): void
	{
		$cookies = new Cookies;

		$cookies->add('foo', 'bar');

		$foo = $cookies->all()['foo'];

		$this->assertSame('/', $foo['options']['path']);
		$this->assertSame('', $foo['options']['domain']);
		$this->assertSame(false, $foo['options']['secure']);
		$this->assertSame(false, $foo['options']['httponly']);

		$cookies->add('foo', 'bar', 0, ['path' => '/foo', 'domain' => 'example.org', 'secure' => true, 'httponly' => true]);

		$foo = $cookies->all()['foo'];

		$this->assertSame('/foo', $foo['options']['path']);
		$this->assertSame('example.org', $foo['options']['domain']);
		$this->assertSame(true, $foo['options']['secure']);
		$this->assertSame(true, $foo['options']['httponly']);
	}

	/**
	 *
	 */
	public function testSetDefaults(): void
	{
		$cookies = new Cookies;

		$cookies->setOptions(['path' => '/foo', 'domain' => 'example.org', 'secure' => true, 'httponly' => true]);

		$cookies->add('foo', 'bar');

		$foo = $cookies->all()['foo'];

		$this->assertSame('/foo', $foo['options']['path']);
		$this->assertSame('example.org', $foo['options']['domain']);
		$this->assertSame(true, $foo['options']['secure']);
		$this->assertSame(true, $foo['options']['httponly']);
	}

	/**
	 *
	 */
	public function testSetRaw(): void
	{
		$cookies = new Cookies;

		$cookies->add('foo', 'bar', 0, [], true);

		$this->assertTrue($cookies->all()['foo']['raw']);

		$cookies->add('foo', 'bar', 0, []);

		$this->assertFalse($cookies->all()['foo']['raw']);
	}
}
