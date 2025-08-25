<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator;

use mako\tests\TestCase;
use mako\validator\exceptions\ValidationException;
use mako\validator\input\InputInterface;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
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
	public function testGetMessageWithErrors(): void
	{
		$errors =
		[
			'foo' => 'The foo field is required.',
			'bar' => 'The bar field is required.',
		];

		$exception = new ValidationException($errors, 'Invalid input.');

		$this->assertSame('Invalid input: the foo field is required, the bar field is required.', $exception->getMessageWithErrors());
	}

	/**
	 *
	 */
	public function testSetInputAndGetInput(): void
	{
		$exception = new ValidationException([]);

		$input = Mockery::mock(InputInterface::class);

		$exception->setInput($input);

		$this->assertSame($input, $exception->getInput());
	}
}
