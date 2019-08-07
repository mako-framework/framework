<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\input\arguments;

use mako\cli\input\arguments\Argument;
use mako\cli\input\arguments\ArgvParser;
use mako\cli\input\arguments\exceptions\ArgumentException;
use mako\cli\input\arguments\exceptions\InvalidArgumentException;
use mako\cli\input\arguments\exceptions\MissingArgumentException;
use mako\cli\input\arguments\exceptions\UnexpectedValueException;
use mako\tests\TestCase;
use RuntimeException;

/**
 * @group unit
 */
class ArgvParserTest extends TestCase
{
	/**
	 *
	 */
	public function testParser(): void
	{
		$parser = new ArgvParser([]);

		$this->assertSame([], $parser->parse());
	}

	/**
	 *
	 */
	public function testParserWithUnknownPositional(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$this->expectExceptionMessage('Unknown positional argument with value [ script ].');

		$parser = new ArgvParser(['script']);

		$parser->parse();
	}

	/**
	 *
	 */
	public function testParserWithUnknownOption(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$this->expectExceptionMessage('Unknown argument [ --unknown ].');

		$parser = new ArgvParser(['--unknown']);

		$parser->parse();
	}

	/**
	 *
	 */
	public function testParserWithUnknownOptionAndSuggestion(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$this->expectExceptionMessage('Unknown argument [ --hast ]. Did you mean [ --host ]?');

		$parser = new ArgvParser(['--hast'], [new Argument('--host')]);

		$parser->parse();
	}

	/**
	 *
	 */
	public function testAmbiguousArgumentNames(): void
	{
		$this->expectException(RuntimeException::class);

		$this->expectExceptionMessage('Ambiguous argument name. [ --document_root ] will collide with [ --document-root ].');

		new ArgvParser([], [new Argument('--document-root'), new Argument('--document_root')]);
	}

	/**
	 *
	 */
	public function testDuplicateAliases(): void
	{
		$this->expectException(RuntimeException::class);

		$this->expectExceptionMessage('Duplicate alias detected [ -h ]. The alias of [ --host ] will collide with the alias of [ --help ].');

		new ArgvParser([], [new Argument('-h|--help'), new Argument('-h|--host')]);
	}

	/**
	 *
	 */
	public function testExpectedIntegerFailure(): void
	{
		$this->expectException(UnexpectedValueException::class);

		$this->expectExceptionMessage('The [ --test ] argument expects an integer.');

		$parser = new ArgvParser(['--test=xxx'], [new Argument('--test', '', Argument::IS_INT)]);

		$parser->parse();
	}

	/**
	 *
	 */
	public function testExpectedFloatFailure(): void
	{
		$this->expectException(UnexpectedValueException::class);

		$this->expectExceptionMessage('The [ --test ] argument expects a float.');

		$parser = new ArgvParser(['--test=xxx'], [new Argument('--test', '', Argument::IS_FLOAT)]);

		$parser->parse();
	}

	/**
	 *
	 */
	public function testArgumentWithMissingValue(): void
	{
		$this->expectException(ArgumentException::class);

		$this->expectExceptionMessage('Missing value for argument [ --test ].');

		$parser = new ArgvParser(['--test'], [new Argument('--test')]);

		$parser->parse();
	}

	/**
	 *
	 */
	public function testBooleanArgumentWithAValue(): void
	{
		$this->expectException(ArgumentException::class);

		$this->expectExceptionMessage('The [ --test ] argument is a boolean and does not accept values.');

		$parser = new ArgvParser(['--test=123'], [new Argument('--test', '', Argument::IS_BOOL)]);

		$parser->parse();
	}

	/**
	 *
	 */
	public function testMissingRequiredArgument(): void
	{
		$this->expectException(MissingArgumentException::class);

		$this->expectExceptionMessage('Missing required argument [ --test ].');

		$parser = new ArgvParser([], [new Argument('--test')]);

		$parser->parse();
	}

	/**
	 *
	 */
	public function testDifferentTypesOfArguments(): void
	{
		$parser = new ArgvParser(['script', '--bool', 'pos1', 'pos2', 'pos3', '--int=1', '--float', '1.1', '-a123', '-A', '123'],
		[
			new Argument('script'),
			new Argument('array', '', Argument::IS_ARRAY),
			new Argument('--bool', '', Argument::IS_BOOL),
			new Argument('--int', '', Argument::IS_INT),
			new Argument('--float', '', Argument::IS_FLOAT),
			new Argument('-a|--alias1'),
			new Argument('-A|--alias2'),
		]);

		$exptected =
		[
			'script' => 'script',
			'bool'   => true,
			'array'  => ['pos1', 'pos2', 'pos3'],
			'int'    => 1,
			'float'  => 1.1,
			'alias1' => '123',
			'alias2' => '123',
		];

		$this->assertSame($exptected, $parser->parse());
	}

	/**
	 *
	 */
	public function testDisablingOfOptionParsing(): void
	{
		$parser = new ArgvParser(['--bool', '--', '--int', 'foo', 'bar'],
		[
			new Argument('array', '', Argument::IS_ARRAY),
			new Argument('--bool', '', Argument::IS_BOOL),
			new Argument('--int', '', Argument::IS_INT | Argument::IS_OPTIONAL),
		]);

		$exptected =
		[
			'bool'  => true,
			'array' => ['--int', 'foo', 'bar'],
			'int'   => null,
		];

		$this->assertSame($exptected, $parser->parse());
	}

	/**
	 *
	 */
	public function testChainedAliases(): void
	{
		$parser = new ArgvParser(['ls', '-laitfoo'],
		[
			new Argument('script'),
			new Argument('-l|--long', '', Argument::IS_BOOL),
			new Argument('-a|--all', '', Argument::IS_BOOL),
			new Argument('-i|--inode', '', Argument::IS_BOOL),
			new Argument('-t|--test', ''),
		]);

		$expected =
		[
			'script' => 'ls',
			'long'   => true,
			'all'    => true,
			'inode'  => true,
			'test'   => 'foo',
		];

		$this->assertSame($expected, $parser->parse());

		//

		$parser = new ArgvParser(['ls', '-lait', 'foo'],
		[
			new Argument('script'),
			new Argument('-l|--long', '', Argument::IS_BOOL),
			new Argument('-a|--all', '', Argument::IS_BOOL),
			new Argument('-i|--inode', '', Argument::IS_BOOL),
			new Argument('-t|--test', ''),
		]);

		$expected =
		[
			'script' => 'ls',
			'long'   => true,
			'all'    => true,
			'inode'  => true,
			'test'   => 'foo',
		];

		$this->assertSame($expected, $parser->parse());
	}

	/**
	 *
	 */
	public function testGetArgumentValue(): void
	{
		$parser = new ArgvParser(['--test1', 'foobar'],
		[
			new Argument('-t|--test1'),
			new Argument('-T|--test2', '', Argument::IS_OPTIONAL),
		]);

		$this->assertSame('foobar', $parser->getArgumentValue('--test1'));

		$this->assertSame('foobar', $parser->getArgumentValue('-t'));

		$this->assertNull($parser->getArgumentValue('--test2'));

		$this->assertNull($parser->getArgumentValue('-T'));

		$this->assertNull($parser->getArgumentValue('nope'));
	}

	/**
	 *
	 */
	public function testDefaultValues(): void
	{
		$parser = new ArgvParser([],
		[
			new Argument('--bool', '', Argument::IS_BOOL | Argument::IS_OPTIONAL),
			new Argument('--array1', '', Argument::IS_ARRAY | Argument::IS_OPTIONAL),
			new Argument('--array2', '', Argument::IS_ARRAY | Argument::IS_OPTIONAL, ['foo', 'bar']),
			new Argument('--basic1', '', Argument::IS_OPTIONAL),
			new Argument('--basic2', '', Argument::IS_OPTIONAL, 'foo'),
		]);

		$expected =
		[
			'bool'   => false,
			'array1' => [],
			'array2' => ['foo', 'bar'],
			'basic1' => null,
			'basic2' => 'foo',
		];

		$this->assertSame($expected, $parser->parse());
	}

	/**
	 *
	 */
	public function testIgnoreUnknownValues(): void
	{
		$parser = new ArgvParser(['script', '--bool', 'pos1', 'pos2', 'pos3', '--int=1', '--float', '1.1', '-a123', '-A', '123'],
		[
			new Argument('script'),
			new Argument('--bool', '', Argument::IS_BOOL),
			new Argument('--float', '', Argument::IS_FLOAT),
			new Argument('-a|--alias1'),
		]);

		$exptected =
		[
			'script' => 'script',
			'bool'   => true,
			'float'  => 1.1,
			'alias1' => '123',
		];

		$this->assertSame($exptected, $parser->parse(true));
	}
}
