<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\error\handlers\hints;

use ArgumentCountError as ArgumentCountErrorException;
use Exception;
use mako\error\handlers\hints\ArgumentCountError;
use mako\tests\TestCase;
use PHPUnit\Metadata\Group;

#[Group('unit')]
class ArgumentCountErrorTest extends TestCase
{
	protected function testFunction(int $test): void
	{
	}

	/**
	 *
	 */
	public function testWithInvalidExceptionType(): void
	{
		$exception = new Exception('Foobar');

		$hint = new ArgumentCountError;

		$this->assertFalse($hint->canProvideHint($exception));
	}

	/**
	 *
	 */
	public function testWithClosure(): void
	{
		$exception = new ArgumentCountErrorException('Too few arguments to function {closure}()...');

		$hint = new ArgumentCountError;

		$this->assertTrue($hint->canProvideHint($exception));

		$this->assertNull($hint->getHint($exception));
	}

	/**
	 *
	 */
	public function testWithAnonymousClassMethod(): void
	{
		$exception = new ArgumentCountErrorException('Too few arguments to function class@anonymous::foo()...');

		$hint = new ArgumentCountError;

		$this->assertTrue($hint->canProvideHint($exception));

		$this->assertNull($hint->getHint($exception));
	}

	/**
	 *
	 */
	public function testWithClassMethod(): void
	{
		$class = static::class;

		$exception = new ArgumentCountErrorException('Too few arguments to function ' . $class . '::testFunction()...');

		$hint = new ArgumentCountError;

		$this->assertTrue($hint->canProvideHint($exception));

		$this->assertSame(<<<HINT
		The method signature is:

		{$class}::testFunction(int \$test): void
		HINT, $hint->getHint($exception));
	}

	/**
	 *
	 */
	public function testWithFunction(): void
	{
		$class = static::class;

		$exception = new ArgumentCountErrorException('Too few arguments to function strcmp()...');

		$hint = new ArgumentCountError;

		$this->assertTrue($hint->canProvideHint($exception));

		$this->assertSame(<<<'HINT'
		The function signature is:

		strcmp(string $string1, string $string2): int
		HINT, $hint->getHint($exception));
	}
}
