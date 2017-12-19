<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\common;

use PHPUnit_Framework_TestCase;

use mako\common\traits\FunctionParserTrait;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class Parser
{
	use FunctionParserTrait;

	public function parse($function, $namedParameters = null)
	{
		return $this->parseFunction($function, $namedParameters);
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class FunctionParserTraitTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function testBasicFunction()
	{
		$parser = new Parser;

		$this->assertSame(['foo', []], $parser->parse('foo', false));
	}

	/**
	 *
	 */
	public function testBasicFunctionWithAutodetect()
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

		$this->assertSame(['foo', [1]], $parser->parse('foo(1)', false));

		$this->assertSame(['foo', ['1']], $parser->parse('foo("1")', false));

		$this->assertSame(['foo', [true]], $parser->parse('foo(true)', false));

		$this->assertSame(['foo', [false]], $parser->parse('foo(false)', false));
	}

	/**
	 *
	 */
	public function testFunctionWithOneParameterAndAutodetect()
	{
		$parser = new Parser;

		$this->assertSame(['foo', [1]], $parser->parse('foo(1)'));

		$this->assertSame(['foo', ['1']], $parser->parse('foo("1")'));

		$this->assertSame(['foo', [true]], $parser->parse('foo(true)'));

		$this->assertSame(['foo', [false]], $parser->parse('foo(false)'));
	}

	/**
	 *
	 */
	public function testFunctionWithMultipleParameters()
	{
		$parser = new Parser;

		$this->assertSame(['foo', [1, 2, 3]], $parser->parse('foo(1,2,3)', false));

		$this->assertSame(['foo', [1, '2', 3]], $parser->parse('foo(1,"2",3)', false));

		$this->assertSame(['foo', [1, 2, 3, ['a', 'b', 'c'], ['bar' => 'baz']]], $parser->parse('foo(1,2,3,["a","b","c"],{"bar":"baz"})', false));
	}

	/**
	 *
	 */
	public function testFunctionWithMultipleParametersAndAutodetect()
	{
		$parser = new Parser;

		$this->assertSame(['foo', [1, 2, 3]], $parser->parse('foo(1,2,3)'));

		$this->assertSame(['foo', [1, '2', 3]], $parser->parse('foo(1,"2",3)'));

		$this->assertSame(['foo', [1, 2, 3, ['a', 'b', 'c'], ['bar' => 'baz']]], $parser->parse('foo(1,2,3,["a","b","c"],{"bar":"baz"})'));
	}

	/**
	 *
	 */
	public function testFunctionWithNamedParameters()
	{
		$parser = new Parser;

		$this->assertSame(['foo', ['a' => 1, 'b' => 2]], $parser->parse('foo("a":1,"b":2)', true));
	}

	/**
	 *
	 */
	public function testFunctionWithNamedParametersAndAutodetect()
	{
		$parser = new Parser;

		$this->assertSame(['foo', ['a' => 1, 'b' => 2]], $parser->parse('foo("a":1,"b":2)'));
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testFunctionWithInvalidJson()
	{
		$parser = new Parser;

		$parser->parse('foo(bar)');
	}
}
