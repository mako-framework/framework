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
	public function testCountable(): void
	{
		$headers = new Headers;

		$this->assertSame(0, count($headers));

		$headers->add('foo', 'bar');

		$this->assertSame(1, count($headers));
	}

	/**
	 *
	 */
	public function testIterable(): void
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
	public function testHas(): void
	{
		$headers = new Headers;

		$this->assertFalse($headers->has('foo'));

		$headers->add('foo', 'bar');

		$this->assertTrue($headers->has('foo'));
	}

	/**
	 *
	 */
	public function testHasValue(): void
	{
		$headers = new Headers;

		$headers->add('Cache-Control', 'private');

		$this->assertTrue($headers->hasValue('Cache-Control', 'private'));

		$this->assertTrue($headers->hasValue('cache-control', 'PRIVATE', false));

		$this->assertFalse($headers->hasValue('cache-control', 'PRIVATE'));

		$this->assertFalse($headers->hasValue('Cache-Control', 'no-cache'));
	}

	/**
	 *
	 */
	public function testRemove(): void
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
	public function testClear(): void
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
	public function testClearExcept(): void
	{
		$headers = new Headers;

		$headers->add('Access-Control-Allow-Origin', '*');

		$headers->add('X-Custom-Header-1', 'foo');

		$headers->add('X-Custom-Header-2', 'bar');

		$this->assertSame(3, count($headers));

		$headers->clearExcept(['Access-Control-.*', 'X-Custom-Header-2']);

		$this->assertSame(2, count($headers));

		$this->assertSame(['Access-Control-Allow-Origin', 'X-Custom-Header-2'], array_keys($headers->all()));
	}

	/**
	 *
	 */
	public function testAddMultiple(): void
	{
		$headers = new Headers;

		$headers->add('foo', 'bar');

		$headers->add('foo', 'baz', false);

		$this->assertSame(['foo' => ['bar', 'baz']], $headers->all());
	}

	/**
	 *
	 */
	public function testAddOverride(): void
	{
		$headers = new Headers;

		$headers->add('foo', 'bar');

		$headers->add('foo', 'baz');

		$this->assertSame(['foo' => ['baz']], $headers->all());
	}
}
