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

/**
 * @group unit
 */
class CookiesTest extends TestCase
{
	/**
	 *
	 */
	public function testCountable()
	{
		$cookies = new Cookies;

		$this->assertSame(0, count($cookies));

		$cookies->add('foo', 'bar');

		$this->assertSame(1, count($cookies));
	}

	/**
	 *
	 */
	public function testIterable()
	{
		$cookies = new Cookies;

		$cookies->add('foo', 'bar');

		foreach($cookies as $cookie)
		{
			$this->assertTrue(is_array($cookie));

			$this->assertArrayHasKey('name', $cookie);
			$this->assertArrayHasKey('value', $cookie);
			$this->assertArrayHasKey('ttl', $cookie);
			$this->assertArrayHasKey('path', $cookie);
			$this->assertArrayHasKey('domain', $cookie);
			$this->assertArrayHasKey('secure', $cookie);
			$this->assertArrayHasKey('httponly', $cookie);
		}
	}

	/**
	 *
	 */
	public function testAddSigned()
	{
		$signer = Mockery::mock(Signer::class);

		$signer->shouldReceive('sign')->once()->with('value')->andReturn('signedvalue');

		$cookies = new Cookies($signer);

		$cookies->addSigned('foo', 'value');

		$this->assertSame('signedvalue', $cookies->all()['foo']['value']);
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage A [ Signer ] instance is required to sign cookies.
	 */
	public function testAddSignedWithoutSigner()
	{
		$cookies = new Cookies;

		$cookies->addSigned('foo', 'value');
	}

	/**
	 *
	 */
	public function testHas()
	{
		$cookies = new Cookies;

		$this->assertFalse($cookies->has('foo'));

		$cookies->add('foo', 'bar');

		$this->assertTrue($cookies->has('foo'));
	}

	/**
	 *
	 */
	public function testRemove()
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
	public function testDelete()
	{
		$cookies = new Cookies;

		$cookies->delete('foo');

		$this->assertTrue($cookies->all()['foo']['ttl'] + 100 < time());
	}

	/**
	 *
	 */
	public function testClear()
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
	public function testAddWithOptions()
	{
		$cookies = new Cookies;

		$cookies->add('foo', 'bar');

		$foo = $cookies->all()['foo'];

		$this->assertSame('/', $foo['path']);
		$this->assertSame('', $foo['domain']);
		$this->assertSame(false, $foo['secure']);
		$this->assertSame(false, $foo['httponly']);

		$cookies->add('foo', 'bar', 0, ['path' => '/foo', 'domain' => 'example.org', 'secure' => true, 'httponly' => true]);

		$foo = $cookies->all()['foo'];

		$this->assertSame('/foo', $foo['path']);
		$this->assertSame('example.org', $foo['domain']);
		$this->assertSame(true, $foo['secure']);
		$this->assertSame(true, $foo['httponly']);
	}

	/**
	 *
	 */
	public function testSetDefaults()
	{
		$cookies = new Cookies;

		$cookies->setOptions(['path' => '/foo', 'domain' => 'example.org', 'secure' => true, 'httponly' => true]);

		$cookies->add('foo', 'bar');

		$foo = $cookies->all()['foo'];

		$this->assertSame('/foo', $foo['path']);
		$this->assertSame('example.org', $foo['domain']);
		$this->assertSame(true, $foo['secure']);
		$this->assertSame(true, $foo['httponly']);
	}
}
