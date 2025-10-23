<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\error\handlers\hints;

use ErrorException;
use Exception;
use mako\error\handlers\hints\UndefinedProperty;
use mako\tests\TestCase;
use PHPUnit\Metadata\Group;

#[Group('unit')]
class UndefinedPropertyTest extends TestCase
{
	protected $testProperty;

	/**
	 *
	 */
	public function testWithInvalidMessage(): void
	{
		$exception = new ErrorException('Foobar');

		$hint = new UndefinedProperty;

		$this->assertFalse($hint->canProvideHint($exception));

		$this->assertNull($hint->getHint($exception));
	}

	/**
	 *
	 */
	public function testWithInvalidExceptionType(): void
	{
		$exception = new Exception('Undefined property: class@anonymous::$foo');

		$hint = new UndefinedProperty;

		$this->assertFalse($hint->canProvideHint($exception));
	}

	/**
	 *
	 */
	public function testWithAnonymousClass(): void
	{
		$exception = new ErrorException('Undefined property: class@anonymous::$foo');

		$hint = new UndefinedProperty;

		$this->assertTrue($hint->canProvideHint($exception));

		$this->assertNull($hint->getHint($exception));
	}

	/**
	 *
	 */
	public function testWithStdClass(): void
	{
		$exception = new ErrorException('Undefined property: stdClass::$foo');

		$hint = new UndefinedProperty;

		$this->assertTrue($hint->canProvideHint($exception));

		$this->assertNull($hint->getHint($exception));
	}

	/**
	 *
	 */
	public function testWithValidClass(): void
	{
		$exception = new ErrorException('Undefined property: ' . __CLASS__ . '::$testPraperty');

		$hint = new UndefinedProperty;

		$this->assertTrue($hint->canProvideHint($exception));

		$this->assertSame('Did you mean to access the ' . __CLASS__ . '::$testProperty property?', $hint->getHint($exception));
	}
}
