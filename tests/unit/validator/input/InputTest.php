<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\input;

use mako\tests\TestCase;
use mako\validator\input\Input;
use mako\validator\Validator;
use Mockery;

/**
 * @group unit
 */
class InputTest extends TestCase
{
	/**
	 *
	 */
	public function testGetRules(): void
	{
		$input = new class extends Input
		{
			protected $rules = ['foo' => 'bar'];

			public function getInput(): array { return []; }
		};

		$this->assertSame(['foo' => 'bar'], $input->getRules());
	}

	/**
	 *
	 */
	public function testGetExtensions(): void
	{
		$input = new class extends Input
		{
			protected $extensions = ['bar' => 'foo'];

			public function getInput(): array { return []; }
		};

		$this->assertSame(['bar' => 'foo'], $input->getExtensions());
	}

	/**
	 *
	 */
	public function testAddConditionalRules(): void
	{
		$input = new class extends Input
		{
			public function getInput(): array { return []; }
		};

		$validator = Mockery::mock(Validator::class);

		$validator->shouldReceive('addRule')->never();

		$input->addConditionalRules($validator);
	}

	/**
	 *
	 */
	public function testGetErrorMessage(): void
	{
		$input = new class extends Input
		{
			public function getInput(): array { return []; }
		};

		$this->assertNull($input->getErrorMessage());

		$input = new class extends Input
		{
			protected $errorMessage = 'Invalid input.';

			public function getInput(): array { return []; }
		};

		$this->assertSame('Invalid input.', $input->getErrorMessage());
	}
}
