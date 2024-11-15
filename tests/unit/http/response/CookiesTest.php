<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http;

use mako\http\exceptions\HttpException;
use mako\http\response\Cookies;
use mako\security\Signer;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
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

		foreach ($cookies as $cookie) {
			$this->assertTrue(is_array($cookie));

			$this->assertArrayHasKey('raw', $cookie);
			$this->assertArrayHasKey('name', $cookie);
			$this->assertArrayHasKey('value', $cookie);
			$this->assertArrayHasKey('expires', $cookie['options']);
			$this->assertArrayHasKey('path', $cookie['options']);
			$this->assertArrayHasKey('domain', $cookie['options']);
			$this->assertArrayHasKey('secure', $cookie['options']);
			$this->assertArrayHasKey('httponly', $cookie['options']);
			$this->assertArrayHasKey('samesite', $cookie['options']);
		}
	}

	/**
	 *
	 */
	public function testAddSigned(): void
	{
		/** @var Mockery\MockInterface|Signer $signer */
		$signer = Mockery::mock(Signer::class);

		$signer->shouldReceive('sign')->once()->with('value')->andReturn('signedvalue');

		$cookies = new Cookies($signer);

		$cookies->addSigned('foo', 'value');

		$foo = $cookies->all()['foo'];

		$this->assertSame('signedvalue', $foo['value']);

		$this->assertFalse($foo['raw']);
	}

   /**
    *
    */
   public function testAddSignedWithRaw(): void
   {
	   /** @var Mockery\MockInterface|Signer $signer */
	   $signer = Mockery::mock(Signer::class);

	   $signer->shouldReceive('sign')->once()->with('value')->andReturn('signedvalue');

	   $cookies = new Cookies($signer);

	   $cookies->addSigned('foo', 'value', 0, [], true);

	   $foo = $cookies->all()['foo'];

	   $this->assertSame('signedvalue', $foo['value']);

	   $this->assertTrue($foo['raw']);
   }

	/**
	 *
	 */
	public function testAddSignedWithoutSigner(): void
	{
		$this->expectException(HttpException::class);

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
	public function testClearExcept(): void
	{
		$cookies = new Cookies;

		$cookies->add('foo-bar', '1');

		$cookies->add('foo-baz', '2');

		$cookies->add('hello-workd', 3);

		$this->assertSame(3, count($cookies));

		$cookies->clearExcept(['foo-.*', 'hello']);

		$this->assertSame(2, count($cookies));

		$this->assertSame(['foo-bar', 'foo-baz'], array_keys($cookies->all()));
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
		$this->assertSame('Lax', $foo['options']['samesite']);

		$this->assertFalse($foo['raw']);

		$cookies->add('foo', 'bar', 0, ['path' => '/foo', 'domain' => 'example.org', 'secure' => true, 'httponly' => true, 'samesite' => 'Strict']);

		$foo = $cookies->all()['foo'];

		$this->assertSame('/foo', $foo['options']['path']);
		$this->assertSame('example.org', $foo['options']['domain']);
		$this->assertSame(true, $foo['options']['secure']);
		$this->assertSame(true, $foo['options']['httponly']);
		$this->assertSame('Strict', $foo['options']['samesite']);

		$this->assertFalse($foo['raw']);
	}

	/**
	 *
	 */
	public function testAddWithDefaults(): void
	{
		$cookies = new Cookies;

		$cookies->setOptions(['path' => '/foo', 'domain' => 'example.org', 'secure' => true, 'httponly' => true, 'samesite' => 'Strict']);

		$cookies->add('foo', 'bar');

		$foo = $cookies->all()['foo'];

		$this->assertSame('/foo', $foo['options']['path']);
		$this->assertSame('example.org', $foo['options']['domain']);
		$this->assertSame(true, $foo['options']['secure']);
		$this->assertSame(true, $foo['options']['httponly']);
		$this->assertSame('Strict', $foo['options']['samesite']);

		$this->assertFalse($foo['raw']);
	}

	/**
	 *
	 */
	public function testAddWithRaw(): void
	{
		$cookies = new Cookies;

		$cookies->add('foo', 'bar', 0, [], true);

		$this->assertTrue($cookies->all()['foo']['raw']);
	}

	/**
	 *
	 */
	public function testAddRaw(): void
	{
		$cookies = new Cookies;

		$cookies->addRaw('foo', 'bar', 0, []);

		$this->assertTrue($cookies->all()['foo']['raw']);
	}

	/**
	 *
	 */
	public function testAddRawSigned(): void
	{
		/** @var Mockery\MockInterface|Signer $signer */
		$signer = Mockery::mock(Signer::class);

		$signer->shouldReceive('sign')->once()->with('bar')->andReturn('signedbar');

		$cookies = new Cookies($signer);

		$cookies->addRawSigned('foo', 'bar', 0, []);

		$foo = $cookies->all()['foo'];

		$this->assertTrue($foo['raw']);

		$this->assertSame('signedbar', $foo['value']);
	}
}
