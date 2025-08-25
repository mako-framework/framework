<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\request;

use mako\http\request\Cookies;
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
	public function testCountEmptySet(): void
	{
		$cookies = new Cookies;

		$this->assertSame(0, count($cookies));
	}

	/**
	 *
	 */
	public function testCountSet(): void
	{
		$cookies = new Cookies(['foo' => 'bar']);

		$this->assertSame(1, count($cookies));
	}

	/**
	 *
	 */
	public function testIterateEmpySet(): void
	{
		$cookies = new Cookies;

		$iterations = 0;

		foreach ($cookies as $cookie) {
			$iterations++;
		}

		$this->assertSame(0, $iterations);
	}

	/**
	 *
	 */
	public function testIterateSet(): void
	{
		$cookies = new Cookies(['foo' => 'bar']);

		$iterations = 0;

		foreach ($cookies as $cookie) {
			$iterations++;
		}

		$this->assertSame(1, $iterations);
	}

	/**
	 *
	 */
	public function testAdd(): void
	{
		$cookies = new Cookies;

		$this->assertSame(0, count($cookies));

		$cookies->add('foo', 'bar');

		$this->assertSame(1, count($cookies));
	}

	/**
	 *
	 */
	public function testAddSigned(): void
	{
		$signer = Mockery::mock(Signer::class);

		$signer->shouldReceive('sign')->once()->with('bar')->andReturn('signedbar');

		$cookies = new Cookies([], $signer);

		$this->assertSame(0, count($cookies));

		$cookies->addSigned('foo', 'bar');

		$this->assertSame(1, count($cookies));
	}

	/**
	 *
	 */
	public function testHas(): void
	{
		$cookies = new Cookies(['foo' => 'bar']);

		$this->assertTrue($cookies->has('foo'));

		$this->assertFalse($cookies->has('bar'));
	}

	/**
	 *
	 */
	public function testGet(): void
	{
		$cookies = new Cookies(['foo' => 'bar']);

		$this->assertSame('bar', $cookies->get('foo'));

		$this->assertNull($cookies->get('bar'));

		$this->assertFalse($cookies->get('bar', false));
	}

	/**
	 *
	 */
	public function testGetSigned(): void
	{
		$signer = Mockery::mock(Signer::class);

		$signer->shouldReceive('validate')->once()->with('signedbar')->andReturn('bar');

		$cookies = new Cookies(['foo' => 'signedbar'], $signer);

		$this->assertSame('bar', $cookies->getSigned('foo'));

		$this->assertNull($cookies->getSigned('bar'));

		$this->assertFalse($cookies->getSigned('bar', false));
	}

	/**
	 *
	 */
	public function testRemove(): void
	{
		$cookies = new Cookies(['foo' => 'bar']);

		$this->assertSame(1, count($cookies));

		$cookies->remove('foo');

		$this->assertSame(0, count($cookies));
	}

	/**
	 *
	 */
	public function testAll(): void
	{
		$cookies = new Cookies(['foo' => 'bar']);

		$this->assertSame(['foo' => 'bar'], $cookies->all());
	}
}
