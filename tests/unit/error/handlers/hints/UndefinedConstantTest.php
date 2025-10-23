<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\error\handlers\hints;

use Exception;
use mako\error\handlers\hints\UndefinedConstant;
use mako\tests\TestCase;
use PHPUnit\Metadata\Group;

#[Group('unit')]
class UndefinedConstantTest extends TestCase
{
	protected const int TEST_CONSTANT = 1;

	/**
	 *
	 */
	public function testWithInvalidMessage(): void
	{
		$exception = new Exception('Foobar');

		$hint = new UndefinedConstant;

		$this->assertFalse($hint->canProvideHint($exception));

		$this->assertNull($hint->getHint($exception));
	}

	/**
	 *
	 */
	public function testWithAnonymousClassConstant(): void
	{
		$exception = new Exception('Undefined constant class@anonymous::FOO');

		$hint = new UndefinedConstant;

		$this->assertTrue($hint->canProvideHint($exception));

		$this->assertNull($hint->getHint($exception));
	}

	/**
	 *
	 */
	public function testWithClassConstant(): void
	{
		$exception = new Exception('Undefined constant ' . static::class . '::TEST_CANSTANT');

		$hint = new UndefinedConstant;

		$this->assertTrue($hint->canProvideHint($exception));

		$this->assertSame('Did you mean to use the ' . static::class . '::TEST_CONSTANT constant?', $hint->getHint($exception));
	}

	/**
	 *
	 */
	public function testWithConstant(): void
	{
		$exception = new Exception('Undefined constant "PHP_VARSION"');

		$hint = new UndefinedConstant;

		$this->assertTrue($hint->canProvideHint($exception));

		$this->assertSame('Did you mean to use the PHP_VERSION constant?', $hint->getHint($exception));
	}
}
