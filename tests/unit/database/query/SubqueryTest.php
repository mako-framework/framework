<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query;

use mako\database\query\Subquery;
use mako\tests\TestCase;

/**
 * @group unit
 */
class SubqueryTest extends TestCase
{
	/**
	 *
	 */
	public function testQuery(): void
	{
		$query = function(): void
		{

		};

		$subquery = new Subquery($query);

		$this->assertSame($query, $subquery->getQuery());
	}

	/**
	 *
	 */
	public function testGetAlias(): void
	{
		$query = function(): void
		{

		};

		$subquery = new Subquery($query);

		$this->assertNull($subquery->getAlias());

		//

		$subquery = new Subquery($query, 'foo');

		$this->assertSame('foo', $subquery->getAlias());

		//

		$subquery = (new Subquery($query))->as('bar');

		$this->assertSame('bar', $subquery->getAlias());
	}
}
