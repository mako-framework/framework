<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration\database\midgard;

use ORMTestCase;

use mako\database\midgard\ORM;
use mako\database\midgard\traits\NullableTrait;

class NullableTest extends ORMTestCase
{
	/**
	 * Returns a ORM instance.
	 *
	 * @return \mako\database\midgard\ORM
	 */
	protected function getNullable(): ORM
	{
		return new class extends ORM
		{
			use NullableTrait;

			protected $tableName = 'nullables';

			protected $nullables = ['value1'];
		};
	}

	/**
	 *
	 */
	public function testInsert()
	{
		$nullable = $this->getNullable();

		//

		$nullable->value1 = '';

		$nullable->value2 = '';

		$nullable->save();

		//

		$row = $nullable->get($nullable->id);

		$this->assertNull($row->value1);

		$this->assertSame('', $row->value2);
	}

	/**
	 *
	 */
	public function testUpdate()
	{
		$nullable = $this->getNullable();

		//

		$row = $nullable->get(1);

		$row->value1 = 'foobar';

		$row->value2 = 'barfoo';

		$row->save();

		//

		$row = $nullable->get(1);

		$this->assertEquals('foobar', $row->value1);

		$this->assertEquals('barfoo', $row->value2);

		$row->value1 = '';

		$row->value2 = '';

		$row->save();

		//

		$row = $nullable->get(1);

		$this->assertNull($row->value1);

		$this->assertSame('', $row->value2);
	}
}
