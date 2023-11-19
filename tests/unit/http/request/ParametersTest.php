<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\request;

use mako\http\request\Parameters;
use mako\tests\TestCase;

/**
 * @group unit
 */
class ParametersTest extends TestCase
{
	/**
	 *
	 */
	public function testCountEmptySet(): void
	{
		$parameters = new Parameters;

		$this->assertSame(0, count($parameters));
	}

	/**
	 *
	 */
	public function testCountSet(): void
	{
		$parameters = new Parameters(['foo' => 'bar']);

		$this->assertSame(1, count($parameters));
	}

	/**
	 *
	 */
	public function testIterateEmpySet(): void
	{
		$parameters = new Parameters;

		$iterations = 0;

		foreach ($parameters as $parameter) {
			$iterations++;
		}

		$this->assertSame(0, $iterations);
	}

	/**
	 *
	 */
	public function testIterateSet(): void
	{
		$parameters = new Parameters(['foo' => 'bar']);

		$iterations = 0;

		foreach ($parameters as $parameter) {
			$iterations++;
		}

		$this->assertSame(1, $iterations);
	}

	/**
	 *
	 */
	public function testAdd(): void
	{
		$parameters = new Parameters;

		$this->assertSame(0, count($parameters));

		$parameters->add('foo', 'bar');

		$this->assertSame(1, count($parameters));
	}

	/**
	 *
	 */
	public function testHas(): void
	{
		$parameters = new Parameters(['foo' => 'bar']);

		$this->assertTrue($parameters->has('foo'));

		$this->assertFalse($parameters->has('bar'));
	}

	/**
	 *
	 */
	public function testGet(): void
	{
		$parameters = new Parameters(['foo' => 'bar']);

		$this->assertSame('bar', $parameters->get('foo'));

		$this->assertNull($parameters->get('bar'));

		$this->assertFalse($parameters->get('bar', false));
	}

	/**
	 *
	 */
	public function testRemove(): void
	{
		$parameters = new Parameters(['foo' => 'bar']);

		$this->assertSame(1, count($parameters));

		$parameters->remove('foo');

		$this->assertSame(0, count($parameters));
	}

	/**
	 *
	 */
	public function testAll(): void
	{
		$parameters = new Parameters(['foo' => 'bar']);

		$this->assertSame(['foo' => 'bar'], $parameters->all());
	}

	/**
	 *
	 */
	public function testWhitelisted(): void
	{
		$parameters = new Parameters(['foo' => 'bar', 'baz' => 'bax']);

		$this->assertSame(['foo' => 'bar'], $parameters->whitelisted(['foo']));

		//

		$parameters = new Parameters(['foo' => 'bar', 'baz' => 'bax']);

		$this->assertSame(['baz' => 'bax'], $parameters->whitelisted(['baz']));

		//

		$parameters = new Parameters([]);

		$this->assertSame(['foo' => 'default'], $parameters->whitelisted(['foo'], ['foo' => 'default']));
	}

	/**
	 *
	 */
	public function testBlacklisted(): void
	{
		$parameters = new Parameters(['foo' => 'bar', 'baz' => 'bax']);

		$this->assertSame(['baz' => 'bax'], $parameters->blacklisted(['foo']));

		//

		$parameters = new Parameters(['foo' => 'bar', 'baz' => 'bax']);

		$this->assertSame(['foo' => 'bar'], $parameters->blacklisted(['baz']));

		//

		$parameters = new Parameters([]);

		$this->assertSame(['foo' => 'default'], $parameters->blacklisted(['bar'], ['foo' => 'default']));
	}
}
