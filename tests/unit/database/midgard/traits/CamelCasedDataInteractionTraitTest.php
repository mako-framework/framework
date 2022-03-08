<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\midgard\traits;

use mako\database\exceptions\DatabaseException;
use mako\database\midgard\traits\CamelCasedDataInteractionTrait;
use mako\tests\TestCase;

/**
 * @group unit
 *
 * This only tests parts of the trait since it is meant to be used in a ORM context.
 */
class CamelCasedDataInteractionTraitTest extends TestCase
{
	protected function getClass(): object
	{
		return new class
		{
			use CamelCasedDataInteractionTrait;

			public $columns =
			[
				'foo_bar' => 'foo bar',
				'bar_foo' => 'bar foo',
				'baz_bax' => null,
			];

			public $related =
			[
				'rFooBar' => 'r foo bar',
				'rBarFoo' => 'r bar foo',
				'rBazBax' => null,
			];

			public function __get(string $name)
			{
				return $this->getValue($name);
			}

			public function __set(string $name, $value): void
			{
				$this->setColumnValue($name, $value);
			}

			protected function cast($name, $value)
			{
				return $value;
			}

			protected function fooBarMutator($value)
			{
				return "mutated {$value}";
			}

			protected function fooBarAccessor($value)
			{
				return "accessed {$value}";
			}

			protected function isRelation($name): bool
			{
				return in_array($name, array_keys($this->related));
			}
		};
	}

	/**
	 *
	 */
	public function testMagicGet(): void
	{
		$class = $this->getClass();

		$this->assertSame('accessed foo bar', $class->foo_bar);
		$this->assertSame('accessed foo bar', $class->fooBar);

		$this->assertSame('bar foo', $class->bar_foo);
		$this->assertSame('bar foo', $class->barFoo);

		$this->assertNull($class->baz_bax);
		$this->assertNull($class->bazBax);

		$this->assertSame('r foo bar', $class->rFooBar);

		$this->assertSame('r bar foo', $class->rBarFoo);

		$this->assertNull($class->rBazBax);
	}

	/**
	 *
	 */
	public function testMagicGetOnNonExisingProperty(): void
	{
		$class = $this->getClass();

		$this->expectException(DatabaseException::class);

		$this->expectExceptionMessage('Unknown column or relation [ nonExisingProperty ].');

		$class->nonExisingProperty;
	}

	/**
	 *
	 */
	public function testMagicSet(): void
	{
		$class = $this->getClass();

		$class->foo_bar = 'new value 1';

		$this->assertSame('mutated new value 1', $class->columns['foo_bar']);

		$class->fooBar = 'new value 2';

		$this->assertSame('mutated new value 2', $class->columns['foo_bar']);

		$class->bar_foo = 'new value 1';

		$this->assertSame('new value 1', $class->columns['bar_foo']);

		$class->barFoo = 'new value 2';

		$this->assertSame('new value 2', $class->columns['bar_foo']);
	}

	/**
	 *
	 */
	public function testMagicIsset(): void
	{
		$class = $this->getClass();

		$this->assertTrue(isset($class->foo_bar));
		$this->assertTrue(isset($class->fooBar));

		$this->assertTrue(isset($class->bar_foo));
		$this->assertTrue(isset($class->barFoo));

		$this->assertFalse(isset($class->baz_bax));
		$this->assertFalse(isset($class->bazBax));

		$this->assertFalse(isset($class->nonExisingProperty));
	}

	/**
	 *
	 */
	public function testMagicUnset(): void
	{
		$class = $this->getClass();

		unset($class->foo_bar, $class->barFoo, $class->r_foo_bar, $class->rBarFoo, $class->rFooBar);

		$this->assertSame(['baz_bax' => null], $class->columns);

		$this->assertSame(['rBazBax' => null], $class->related);
	}
}
