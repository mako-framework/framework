<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http;

use mako\http\response\Headers;
use mako\tests\TestCase;

/**
 * @group unit
 */
class HeadersTest extends TestCase
{
	/**
	 *
	 */
	public function testCountable()
	{
		$headers = new Headers;

		$this->assertSame(0, count($headers));

		$headers->add('foo', 'bar');

		$this->assertSame(1, count($headers));
	}

	/**
	 *
	 */
	public function testIterable()
	{
		$headers = new Headers;

		$headers->add('foo', 'bar');

		foreach($headers as $name => $values)
		{
			$this->assertSame('foo', $name);

			$this->assertSame(['bar'], $values);
		}
	}

	/**
	 *
	 */
	public function testHas()
	{
		$headers = new Headers;

		$this->assertFalse($headers->has('foo'));

		$headers->add('foo', 'bar');

		$this->assertTrue($headers->has('foo'));
	}

	/**
	 *
	 */
	public function testRemove()
	{
		$headers = new Headers;

		$headers->add('foo', 'bar');

		$this->assertTrue($headers->has('foo'));

		$headers->remove('foo');

		$this->assertFalse($headers->has('foo'));
	}

	/**
	 *
	 */
	public function testClear()
	{
		$headers = new Headers;

		$headers->add('foo', 'bar');

		$this->assertSame(1, count($headers));

		$headers->clear();

		$this->assertSame(0, count($headers));
	}

	/**
	 *
	 */
	public function testAddMultiple()
	{
		$headers = new Headers;

		$headers->add('foo', 'bar');

		$headers->add('foo', 'baz', false);

		$this->assertSame(['foo' => ['bar', 'baz']], $headers->all());
	}

	/**
	 *
	 */
	public function testAddOverride()
	{
		$headers = new Headers;

		$headers->add('foo', 'bar');

		$headers->add('foo', 'baz');

		$this->assertSame(['foo' => ['baz']], $headers->all());
	}
}
