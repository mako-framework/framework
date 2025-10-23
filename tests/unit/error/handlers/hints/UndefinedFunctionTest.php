<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\error\handlers\hints;

use Exception;
use mako\error\handlers\hints\UndefinedFunction;
use mako\tests\TestCase;
use PHPUnit\Metadata\Group;

#[Group('unit')]
class UndefinedFunctionTest extends TestCase
{
	/**
	 *
	 */
	public function testWithInvalidMessage(): void
	{
		$exception = new Exception('Foobar');

		$hint = new UndefinedFunction;

		$this->assertFalse($hint->canProvideHint($exception));

		$this->assertNull($hint->getHint($exception));
	}

	/**
	 *
	 */
	public function testWithValidFunction(): void
	{
		$exception = new Exception('Call to undefined function strcnp()');

		$hint = new UndefinedFunction;

		$this->assertTrue($hint->canProvideHint($exception));

		$this->assertSame('Did you mean to call the strcmp() function?', $hint->getHint($exception));
	}
}
