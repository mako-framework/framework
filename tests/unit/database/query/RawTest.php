<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query;

use mako\database\query\Raw;
use mako\tests\TestCase;

/**
 * @group unit
 */
class RawTest extends TestCase
{
	/**
	 *
	 */
	public function testGet(): void
	{
		$sql = 'SELECT * FROM "foo"';

		$raw = new Raw($sql);

		$this->assertSame($sql, $raw->getSql());
	}

	/**
	 *
	 */
	public function testGetParameters(): void
	{
		$params = [1, 2, 3];

		$raw = new Raw('', $params);

		$this->assertSame($params, $raw->getParameters());
	}
}
