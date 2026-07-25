<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\env;

use mako\env\DotenvLoader;
use mako\env\exceptions\EnvException;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class DotenvLoaderTest extends TestCase
{
	/**
	 *
	 */
	public function testLoadBasic(): void
	{
		(new DotenvLoader)
		->load(__DIR__ . '/fixtures/basic.env', 'MAKO_TEST_');

		$this->assertSame('value#1', $_ENV['MAKO_TEST_KEY1']);
		$this->assertSame('value#2', $_ENV['MAKO_TEST_KEY2']);
		$this->assertSame('value#3', $_ENV['MAKO_TEST_KEY3']);
		$this->assertSame('var:${MAKO_TEST_KEY1}', $_ENV['MAKO_TEST_KEY4']);
		$this->assertSame('var:${MAKO_TEST_KEY1}', $_ENV['MAKO_TEST_KEY5']);
		$this->assertSame('var:${MAKO_TEST_KEY1}', $_ENV['MAKO_TEST_KEY6']);
		$this->assertSame('value#7', $_ENV['MAKO_TEST_KEY7']);
		$this->assertSame('value#8', $_ENV['MAKO_TEST_KEY8']);
		$this->assertSame('value#9', $_ENV['MAKO_TEST_KEY9']);
		$this->assertSame('value#10', $_ENV['MAKO_TEST_KEY10']);
		$this->assertSame('value#11', $_ENV['MAKO_TEST_KEY11']);
		$this->assertSame('value#12', $_ENV['MAKO_TEST_KEY12']);
		$this->assertSame('value#13', $_ENV['MAKO_TEST_KEY13']);
		$this->assertSame('value#14', $_ENV['MAKO_TEST_KEY14']);
		$this->assertSame('value#15', $_ENV['MAKO_TEST_KEY15']);
		$this->assertSame('foo\nbar', $_ENV['MAKO_TEST_KEY16']);
		$this->assertSame('foo\nbar', $_ENV['MAKO_TEST_KEY17']);
		$this->assertSame("foo\nbar", $_ENV['MAKO_TEST_KEY18']);
		$this->assertSame('var:\${MAKO_TEST_KEY1}', $_ENV['MAKO_TEST_KEY19']);
		$this->assertSame('var:\${MAKO_TEST_KEY1}', $_ENV['MAKO_TEST_KEY20']);
		$this->assertSame('var:\${MAKO_TEST_KEY1}', $_ENV['MAKO_TEST_KEY21']);
		$this->assertSame('var:\\\\${MAKO_TEST_KEY1}', $_ENV['MAKO_TEST_KEY22']);
		$this->assertSame('var:\\\\${MAKO_TEST_KEY1}', $_ENV['MAKO_TEST_KEY23']);
		$this->assertSame('var:\${MAKO_TEST_KEY1}', $_ENV['MAKO_TEST_KEY24']);
		$this->assertSame('value#25', $_ENV['MAKO_TEST_KEY25']);
		$this->assertSame('value#26', $_ENV['MAKO_TEST_KEY26']);
		$this->assertSame('value#27', $_ENV['MAKO_TEST_KEY27']);

		foreach (range(1, 27) as $num) {
			unset($_ENV["MAKO_TEST_KEY{$num}"]);
		}
	}

	/**
	 *
	 */
	public function testLoadBasicWithInterpolatedVariables(): void
	{
		(new DotenvLoader(interpolateVariables: true))
		->load(__DIR__ . '/fixtures/basic.env', 'MAKO_TEST_');

		$this->assertSame('value#1', $_ENV['MAKO_TEST_KEY1']);
		$this->assertSame('value#2', $_ENV['MAKO_TEST_KEY2']);
		$this->assertSame('value#3', $_ENV['MAKO_TEST_KEY3']);
		$this->assertSame('var:${MAKO_TEST_KEY1}', $_ENV['MAKO_TEST_KEY4']);
		$this->assertSame('var:${MAKO_TEST_KEY1}', $_ENV['MAKO_TEST_KEY5']);
		$this->assertSame('var:value#1', $_ENV['MAKO_TEST_KEY6']);
		$this->assertSame('value#7', $_ENV['MAKO_TEST_KEY7']);
		$this->assertSame('value#8', $_ENV['MAKO_TEST_KEY8']);
		$this->assertSame('value#9', $_ENV['MAKO_TEST_KEY9']);
		$this->assertSame('value#10', $_ENV['MAKO_TEST_KEY10']);
		$this->assertSame('value#11', $_ENV['MAKO_TEST_KEY11']);
		$this->assertSame('value#12', $_ENV['MAKO_TEST_KEY12']);
		$this->assertSame('value#13', $_ENV['MAKO_TEST_KEY13']);
		$this->assertSame('value#14', $_ENV['MAKO_TEST_KEY14']);
		$this->assertSame('value#15', $_ENV['MAKO_TEST_KEY15']);
		$this->assertSame('foo\nbar', $_ENV['MAKO_TEST_KEY16']);
		$this->assertSame('foo\nbar', $_ENV['MAKO_TEST_KEY17']);
		$this->assertSame("foo\nbar", $_ENV['MAKO_TEST_KEY18']);
		$this->assertSame('var:\${MAKO_TEST_KEY1}', $_ENV['MAKO_TEST_KEY19']);
		$this->assertSame('var:\${MAKO_TEST_KEY1}', $_ENV['MAKO_TEST_KEY20']);
		$this->assertSame('var:${MAKO_TEST_KEY1}', $_ENV['MAKO_TEST_KEY21']);
		$this->assertSame('var:\\\\${MAKO_TEST_KEY1}', $_ENV['MAKO_TEST_KEY22']);
		$this->assertSame('var:\\\\${MAKO_TEST_KEY1}', $_ENV['MAKO_TEST_KEY23']);
		$this->assertSame('var:\value#1', $_ENV['MAKO_TEST_KEY24']);
		$this->assertSame('value#25', $_ENV['MAKO_TEST_KEY25']);
		$this->assertSame('value#26', $_ENV['MAKO_TEST_KEY26']);
		$this->assertSame('value#27', $_ENV['MAKO_TEST_KEY27']);

		foreach (range(1, 27) as $num) {
			unset($_ENV["MAKO_TEST_KEY{$num}"]);
		}
	}

	/**
	 *
	 */
	public function testNoOverride(): void
	{
		(new DotenvLoader)
		->load(__DIR__ . '/fixtures/override.env', 'MAKO_TEST_');

		$this->assertSame('foo', $_ENV['MAKO_TEST_KEY1']);

		unset($_ENV['MAKO_TEST_KEY1']);
	}

	/**
	 *
	 */
	public function testWithOverride(): void
	{
		(new DotenvLoader(overrideExisting: true))
		->load(__DIR__ . '/fixtures/override.env', 'MAKO_TEST_');

		$this->assertSame('bar', $_ENV['MAKO_TEST_KEY1']);

		unset($_ENV['MAKO_TEST_KEY1']);
	}

	/**
	 *
	 */
	public function testInvalidDeclaration(): void
	{
		$file = __DIR__ . '/fixtures/invalid-declaration.env';

		$this->expectException(EnvException::class);
		$this->expectExceptionMessageIs('Invalid env declaration in [ ' . $file . ' ] on line [ 1 ].');

		(new DotenvLoader)->load($file, 'MAKO_TEST_');
	}

	/**
	 *
	 */
	public function testInvalidKey(): void
	{
		$file = __DIR__ . '/fixtures/invalid-key.env';

		$this->expectException(EnvException::class);
		$this->expectExceptionMessageIs('Invalid key [ $KEY1 ] in [ ' . $file . ' ] on line [ 1 ].');

		(new DotenvLoader)
		->load($file, 'MAKO_TEST_');
	}

	/**
	 *
	 */
	public function testUnterminatedDoubleQuote(): void
	{
		$file = __DIR__ . '/fixtures/unterminated-double-quote.env';

		$this->expectException(EnvException::class);
		$this->expectExceptionMessageIs('Unterminated quoted value in [ ' . $file . ' ] on line [ 1 ].');

		(new DotenvLoader)
		->load($file, 'MAKO_TEST_');
	}

	/**
	 *
	 */
	public function testUnterminatedSingleQuote(): void
	{
		$file = __DIR__ . '/fixtures/unterminated-single-quote.env';

		$this->expectException(EnvException::class);
		$this->expectExceptionMessageIs('Unterminated quoted value in [ ' . $file . ' ] on line [ 1 ].');

		(new DotenvLoader)
		->load($file, 'MAKO_TEST_');
	}

	/**
	 *
	 */
	public function testUndefinedVariable(): void
	{
		$file = __DIR__ . '/fixtures/undefined-variable.env';

		$this->expectException(EnvException::class);
		$this->expectExceptionMessageIs('Undefined environment variable [ $MAKO_TEST_FOO_BAR_BAZ ] in [ ' . $file . ' ] on line [ 1 ].');

		(new DotenvLoader(interpolateVariables: true))
		->load($file, 'MAKO_TEST_');
	}
}
