<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator;

use mako\tests\TestCase;
use mako\validator\ValidatorException;

/**
 * @group unit
 */
class ValidatorExceptionTest extends TestCase
{
	/**
	 *
	 */
	public function testGetErrors(): void
	{
		$errors = ['foo' => 'bar'];

		$exception = new ValidatorException($errors);

		$this->assertSame($errors, $exception->getErrors());
	}

	/**
	 *
	 */
	public function testMeta(): void
	{
		$exception = new ValidatorException([]);

		$exception->addMeta('foo', 'bar');

		$this->assertSame('bar', $exception->getMeta('foo'));

		$this->assertNull($exception->getMeta('bar'));

		$this->assertFalse($exception->getMeta('bar', false));
	}
}
