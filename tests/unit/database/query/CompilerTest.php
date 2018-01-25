<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query;

use mako\database\query\compilers\Compiler;
use mako\tests\TestCase;

/**
 * @group unit
 */
class CompilerTest extends TestCase
{
	/**
	 *
	 */
	public function testSetAndGetDateFormat()
	{
		$format = Compiler::getDateFormat();

		Compiler::setDateFormat('foobar');

		$this->assertSame('foobar', Compiler::getDateFormat());

		Compiler::setDateFormat($format);
	}
}
