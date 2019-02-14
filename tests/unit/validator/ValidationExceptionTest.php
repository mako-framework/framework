<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator;

use mako\tests\TestCase;
use mako\validator\ValidationException;

/**
 * @group unit
 */
class ValidationExceptionTest extends TestCase
{
	/**
	 *
	 */
	public function testGetErrors(): void
	{
		$errors = ['foo' => 'bar'];

		$exception = new ValidationException($errors);

		$this->assertSame($errors, $exception->getErrors());
	}

	/**
	 *
	 */
	public function testMeta(): void
	{
		$exception = new ValidationException([]);

		$exception->addMeta('foo', 'bar');

		$this->assertSame('bar', $exception->getMeta('foo'));

		$this->assertNull($exception->getMeta('bar'));

		$this->assertFalse($exception->getMeta('bar', false));
	}
}
