<?php

namespace mako\tests\unit\common;

use mako\common\FunctionParserTrait;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class Parser
{
	use FunctionParserTrait;

	public function parse($function)
	{
		return $this->parseFunction($function);
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */

class FunctionParserTraitTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function testBasicFunction()
	{
		$parser = new Parser;

		$this->assertSame(['foo', []], $parser->parse('foo'));
	}

	/**
	 *
	 */

	public function testFunctionWithOneParameter()
	{
		$parser = new Parser;

		$this->assertSame(['foo', [1]], $parser->parse('foo:1'));

		$this->assertSame(['foo', ['1']], $parser->parse('foo:"1"'));

		$this->assertSame(['foo', [true]], $parser->parse('foo:true'));

		$this->assertSame(['foo', [false]], $parser->parse('foo:false'));
	}

	/**
	 *
	 */

	public function testFunctionWithMultipleParameters()
	{
		$parser = new Parser;

		$this->assertSame(['foo', [1, 2, 3]], $parser->parse('foo:[1,2,3]'));

		$this->assertSame(['foo', [1, '2', 3]], $parser->parse('foo:[1,"2",3]'));

		$this->assertSame(['foo', [1, 2, 3 ,['a', 'b', 'c']]], $parser->parse('foo:[1,2,3,["a","b","c"]]'));
	}

	/**
	 *
	 */

	public function testFunctionWithNamedParameters()
	{
		$parser = new Parser;

		$this->assertSame(['foo', ['a' => 1, 'b' => 2]], $parser->parse('foo:{"a":1,"b":2}'));
	}

	/**
	 * @expectedException \RuntimeException
	 */

	public function testFunctionWithInvalidJson()
	{
		$parser = new Parser;

		$parser->parse('foo:bar');
	}
}
