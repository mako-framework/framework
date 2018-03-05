<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\request;

use mako\http\request\Cookies;
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
	public function testCountEmptySet()
	{
		$cookies = new Cookies;

		$this->assertSame(0, count($cookies));
	}

	/**
	 *
	 */
	public function testCountSet()
	{
		$cookies = new Cookies(['foo' => 'bar']);

		$this->assertSame(1, count($cookies));
	}

	/**
	 *
	 */
	public function testIterateEmpySet()
	{
		$cookies = new Cookies;

		$iterations = 0;

		foreach($cookies as $cookie)
		{
			$iterations++;
		}

		$this->assertSame(0, $iterations);
	}

	/**
	 *
	 */
	public function testIterateSet()
	{
		$cookies = new Cookies(['foo' => 'bar']);

		$iterations = 0;

		foreach($cookies as $cookie)
		{
			$iterations++;
		}

		$this->assertSame(1, $iterations);
	}

	/**
	 *
	 */
	public function testAdd()
	{
		$cookies = new Cookies;

		$this->assertSame(0, count($cookies));

		$cookies->add('foo', 'bar');

		$this->assertSame(1, count($cookies));
	}

	/**
	 *
	 */
	public function testAddSigned()
	{
		$signer = Mockery::mock('mako\security\Signer');

		$signer->shouldReceive('sign')->once()->with('bar')->andReturn('signedbar');

		$cookies = new Cookies([], $signer);

		$this->assertSame(0, count($cookies));

		$cookies->addSigned('foo', 'bar');

		$this->assertSame(1, count($cookies));
	}

	/**
	 *
	 */
	public function testHas()
	{
		$cookies = new Cookies(['foo' => 'bar']);

		$this->assertTrue($cookies->has('foo'));

		$this->assertFalse($cookies->has('bar'));
	}

	/**
	 *
	 */
	public function testGet()
	{
		$cookies = new Cookies(['foo' => 'bar']);

		$this->assertSame('bar', $cookies->get('foo'));

		$this->assertNull($cookies->get('bar'));

		$this->assertFalse($cookies->get('bar', false));
	}

	/**
	 *
	 */
	public function testGetSigned()
	{
		$signer = Mockery::mock('mako\security\Signer');

		$signer->shouldReceive('validate')->once()->with('signedbar')->andReturn('bar');

		$cookies = new Cookies(['foo' => 'signedbar'], $signer);

		$this->assertSame('bar', $cookies->getSigned('foo'));

		$this->assertNull($cookies->getSigned('bar'));

		$this->assertFalse($cookies->getSigned('bar', false));
	}

	/**
	 *
	 */
	public function testRemove()
	{
		$cookies = new Cookies(['foo' => 'bar']);

		$this->assertSame(1, count($cookies));

		$cookies->remove('foo');

		$this->assertSame(0, count($cookies));
	}

	/**
	 *
	 */
	public function testAll()
	{
		$cookies = new Cookies(['foo' => 'bar']);

		$this->assertSame(['foo' => 'bar'], $cookies->all());
	}
}
