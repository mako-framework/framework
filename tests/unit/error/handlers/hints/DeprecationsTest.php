<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\error\handlers\hints;

use ErrorException;
use mako\error\handlers\hints\Deprecations;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class DeprecationsTest extends TestCase
{
	/**
	 *
	 */
	public function testWithInvalidMessageCode(): void
	{
		$exception = new ErrorException('Foobar', 123);

		$hint = new Deprecations;

		$this->assertFalse($hint->canProvideHint($exception));

		$this->assertNull($hint->getHint($exception));
	}

	/**
	 *
	 */
	public function testWithDeprecation(): void
	{
		$exception = new ErrorException('Foobar', E_DEPRECATED);

		$hint = new Deprecations;

		$this->assertTrue($hint->canProvideHint($exception));

		$this->assertSame(<<<'HINT'
		Update your code to resolve this deprecation. If you intentionally want to ignore this type of deprecation, exclude it from error_reporting. For example:

		error_reporting(E_ALL & ~E_DEPRECATED);

		Alternatively, you may be able to suppress individual deprecations with the @ operator.
		HINT, $hint->getHint($exception));
	}

	/**
	 *
	 */
	public function testWithUserDeprecation(): void
	{
		$exception = new ErrorException('Foobar', E_USER_DEPRECATED);

		$hint = new Deprecations;

		$this->assertTrue($hint->canProvideHint($exception));

		$this->assertSame(<<<'HINT'
		Update your code to resolve this deprecation. If you intentionally want to ignore this type of deprecation, exclude it from error_reporting. For example:

		error_reporting(E_ALL & ~E_USER_DEPRECATED);

		Alternatively, you may be able to suppress individual deprecations with the @ operator.
		HINT, $hint->getHint($exception));
	}
}
