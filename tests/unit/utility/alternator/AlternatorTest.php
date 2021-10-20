<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\utility;

use mako\tests\TestCase;
use mako\utility\str\Alternator;

/**
 * @group unit
 */
class AlternatorTest extends TestCase
{
	/**
	 *
	 */
	public function testToString(): void
	{
		$alternator = new Alternator(['foo', 'bar']);

		$this->assertSame('foo', (string) $alternator);
		$this->assertSame('bar', (string) $alternator);
	}

	/**
	 *
	 */
	public function testInvoke(): void
	{
		$alternator = new Alternator(['foo', 'bar']);

		$this->assertSame('foo', $alternator());
		$this->assertSame('bar', $alternator());
	}
}
