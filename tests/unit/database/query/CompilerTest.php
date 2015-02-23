<?php

namespace mako\tests\unit\database\query;

use mako\database\query\Compiler;

use PHPUnit_Framework_TestCase;

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