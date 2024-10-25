<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\midgard;

use mako\database\midgard\ORM;
use mako\database\midgard\traits\CamelCasedTrait;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class CamelCasedORM extends ORM
{
	use CamelCasedTrait;

	protected array $cast = ['is_something' => 'bool'];

	protected function jsonColumnMutator(array $value)
	{
		return json_encode($value);
	}

	protected function jsonColumnAccessor(string $value): array
	{
		return json_decode($value, true);
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

#[Group('unit')]
class CamelCasedORMTest extends TestCase
{
	/**
	 *
	 */
	public function testProperties(): void
	{
		$model = new CamelCasedORM(['foo_bar' => 1, 'bar_foo' => 2]);

		$this->assertSame(1, $model->fooBar);

		$this->assertSame(1, $model->foo_bar);

		$this->assertSame(2, $model->barFoo);

		$this->assertSame(2, $model->bar_foo);
	}

	/**
	 *
	 */
	public function testToArray(): void
	{
		$model = new CamelCasedORM(['foo_bar' => 1, 'bar_foo' => 2]);

		$this->assertSame(['fooBar' => 1, 'barFoo' => 2], $model->toArray());
	}

	/**
	 *
	 */
	public function testToJson(): void
	{
		$model = new CamelCasedORM(['foo_bar' => 1, 'bar_foo' => 2]);

		$this->assertSame('{"fooBar":1,"barFoo":2}', $model->toJson());

		$this->assertSame('{"fooBar":1,"barFoo":2}', (string) $model);
	}

	/**
	 *
	 */
	public function testPropertiesWithCast(): void
	{
		$model = new CamelCasedORM(['is_something' => 1]);

		$this->assertTrue($model->isSomething);

		$model->is_something = 0;

		$this->assertFalse($model->isSomething);

		//

		$model = new CamelCasedORM(['is_something' => 0]);

		$this->assertFalse($model->isSomething);

		$model->is_something = 1;

		$this->assertTrue($model->isSomething);
	}

	/**
	 *
	 */
	public function testSetAndGetRawColumnValues(): void
	{
		$model = new CamelCasedORM();

		$model->setRawColumnValue('jsonColumn', [1, 2, 3]);

		$this->assertSame([1, 2, 3], $model->getRawColumnValue('json_column'));

		$this->assertSame([1, 2, 3], $model->getRawColumnValue('jsonColumn'));

		$model->setRawColumnValue('json_column', [3, 2, 1]);

		$this->assertSame([3, 2, 1], $model->getRawColumnValue('json_column'));

		$this->assertSame([3, 2, 1], $model->getRawColumnValue('jsonColumn'));

		//

		$model = new CamelCasedORM();

		$model->jsonColumn = [1, 2, 3];

		$this->assertSame('[1,2,3]', $model->getRawColumnValue('json_column'));

		$this->assertSame('[1,2,3]', $model->getRawColumnValue('jsonColumn'));

	}
}
