<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\database\query;

use PHPUnit_Framework_TestCase;

use mako\database\query\compilers\Compiler;

/**
 * @group unit
 */
class CompilerTest extends PHPUnit_Framework_TestCase
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