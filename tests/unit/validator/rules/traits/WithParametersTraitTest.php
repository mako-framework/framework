<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules\traits;

use mako\tests\TestCase;
use mako\validator\rules\traits\WithParametersTrait;

/**
 * @group unit
 */
class WithParametersTraitTest extends TestCase
{
	/**
	 *
	 */
	public function testSetParameters(): void
	{
		$rule = new class
		{
			public $parameters = ['foo', 'bar', 'baz'];

			use WithParametersTrait;
		};

		$rule->setParameters([1, 2]);

		$this->assertSame(['foo' => 1, 'bar' => 2, 'baz' => null], $rule->parameters);
	}

	/**
	 *
	 */
	public function testGetParameter(): void
	{
		$rule = new class
		{
			public $parameters = ['foo', 'bar', 'baz'];

			use WithParametersTrait;

			public function get($name, $optional)
			{
				return $this->getParameter($name, $optional);
			}
		};

		$rule->setParameters([1, 2]);

		$this->assertSame(1, $rule->get('foo', false));
		$this->assertSame(2, $rule->get('bar', false));
		$this->assertNull($rule->get('baz', true));
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionMessageRegExp /Missing required parameter \[ baz \] for validation rule \[ (.*) \]\./
	 */
	public function testGetMissingRequiredParameter(): void
	{
		$rule = new class
		{
			public $parameters = ['foo', 'bar', 'baz'];

			use WithParametersTrait;

			public function get($name)
			{
				return $this->getParameter($name);
			}
		};

		$rule->setParameters([1, 2]);

		$this->assertNull($rule->get('baz'));
	}
}
