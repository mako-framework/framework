<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query;

use PHPUnit_Framework_TestCase;

use mako\database\query\Raw;

/**
 * @group unit
 */
class RawTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function testGet()
	{
		$sql = 'SELECT * FROM "foo"';

		$raw = new Raw($sql);

		$this->assertSame($sql, $raw->get());
	}

	/**
	 *
	 */
	public function testGetParameters()
	{
		$params = [1, 2, 3];

		$raw = new Raw('', $params);

		$this->assertSame($params, $raw->getParameters());
	}
}
