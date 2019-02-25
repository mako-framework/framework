<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\common;

use mako\common\traits\FunctionParserTrait;
use mako\tests\TestCase;
use RuntimeException;

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
class FunctionParserTraitTest extends TestCase
{
	/**
	 *
	 */
	public function testBasicFunction(): void
	{
		$parser = new Parser;

		$this->assertSame(['foo', []], $parser->parse('foo', false));
	}

	/**
	 *
	 */
	public function testBasicFunctionWithAutodetect(): void
	{
		$parser = new Parser;

		$this->assertSame(['foo', []], $parser->parse('foo'));
	}

	/**
	 *
	 */
	public function testFunctionWithOneParameter(): void
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
	public function testFunctionWithOneParameterAndAutodetect(): void
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
	public function testFunctionWithMultipleParameters(): void
	{
		$parser = new Parser;

		$this->assertSame(['foo', [1, 2, 3]], $parser->parse('foo(1,2,3)', false));

		$this->assertSame(['foo', [1, '2', 3]], $parser->parse('foo(1,"2",3)', false));

		$this->assertSame(['foo', [1, 2, 3, ['a', 'b', 'c'], ['bar' => 'baz']]], $parser->parse('foo(1,2,3,["a","b","c"],{"bar":"baz"})', false));
	}

	/**
	 *
	 */
	public function testFunctionWithMultipleParametersAndAutodetect(): void
	{
		$parser = new Parser;

		$this->assertSame(['foo', [1, 2, 3]], $parser->parse('foo(1,2,3)'));

		$this->assertSame(['foo', [1, '2', 3]], $parser->parse('foo(1,"2",3)'));

		$this->assertSame(['foo', [1, 2, 3, ['a', 'b', 'c'], ['bar' => 'baz']]], $parser->parse('foo(1,2,3,["a","b","c"],{"bar":"baz"})'));
	}

	/**
	 *
	 */
	public function testFunctionWithNamedParameters(): void
	{
		$parser = new Parser;

		$this->assertSame(['foo', ['a' => 1, 'b' => 2]], $parser->parse('foo("a":1,"b":2)', true));
	}

	/**
	 *
	 */
	public function testFunctionWithNamedParametersAndAutodetect(): void
	{
		$parser = new Parser;

		$this->assertSame(['foo', ['a' => 1, 'b' => 2]], $parser->parse('foo("a":1,"b":2)'));
	}

	/**
	 *
	 */
	public function testFunctionWithInvalidJson(): void
	{
		$this->expectException(RuntimeException::class);

		$parser = new Parser;

		$parser->parse('foo(bar)');
	}
}
