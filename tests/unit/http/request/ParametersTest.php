<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\request;

use PHPUnit_Framework_TestCase;

use mako\http\request\Parameters;

/**
 * @group unit
 */
class ParametersTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function testCountEmptySet()
	{
		$parameters = new Parameters;

		$this->assertSame(0, count($parameters));
	}

	/**
	 *
	 */
	public function testCountSet()
	{
		$parameters = new Parameters(['foo' => 'bar']);

		$this->assertSame(1, count($parameters));
	}

	/**
	 *
	 */
	public function testIterateEmpySet()
	{
		$parameters = new Parameters;

		$iterations = 0;

		foreach($parameters as $parameter)
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
		$parameters = new Parameters(['foo' => 'bar']);

		$iterations = 0;

		foreach($parameters as $parameter)
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
		$parameters = new Parameters;

		$this->assertSame(0, count($parameters));

		$parameters->add('foo', 'bar');

		$this->assertSame(1, count($parameters));
	}

	/**
	 *
	 */
	public function testHas()
	{
		$parameters = new Parameters(['foo' => 'bar']);

		$this->assertTrue($parameters->has('foo'));

		$this->assertFalse($parameters->has('bar'));
	}

	/**
	 *
	 */
	public function testGet()
	{
		$parameters = new Parameters(['foo' => 'bar']);

		$this->assertSame('bar', $parameters->get('foo'));

		$this->assertNull($parameters->get('bar'));

		$this->assertFalse($parameters->get('bar', false));
	}

	/**
	 *
	 */
	public function testRemove()
	{
		$parameters = new Parameters(['foo' => 'bar']);

		$this->assertSame(1, count($parameters));

		$parameters->remove('foo');

		$this->assertSame(0, count($parameters));
	}

	/**
	 *
	 */
	public function testAll()
	{
		$parameters = new Parameters(['foo' => 'bar']);

		$this->assertSame(['foo' => 'bar'], $parameters->all());
	}
}
