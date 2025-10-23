<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\error\handlers\hints;

use Exception;
use mako\error\handlers\hints\UndefinedMethod;
use mako\tests\TestCase;
use PHPUnit\Metadata\Group;

#[Group('unit')]
class UndefinedMethodTest extends TestCase
{
	/**
	 *
	 */
	public function testWithInvalidMessage(): void
	{
		$exception = new Exception('Foobar');

		$hint = new UndefinedMethod;

		$this->assertFalse($hint->canProvideHint($exception));

		$this->assertNull($hint->getHint($exception));
	}

	/**
	 *
	 */
	public function testWithAnonymousClass(): void
	{
		$exception = new Exception('Call to undefined method class@anonymous::foo()');

		$hint = new UndefinedMethod;

		$this->assertTrue($hint->canProvideHint($exception));

		$this->assertNull($hint->getHint($exception));
	}

	/**
	 *
	 */
	public function testWithValidClass(): void
	{
		$exception = new Exception('Call to undefined method ' . __METHOD__ . 's()');

		$hint = new UndefinedMethod;

		$this->assertTrue($hint->canProvideHint($exception));

		$this->assertSame('Did you mean to call the ' . __METHOD__ . '() method?', $hint->getHint($exception));
	}
}
