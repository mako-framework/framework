<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\input\http\routing\traits;

use mako\http\Request;
use mako\http\request\Files;
use mako\http\request\Parameters;
use mako\tests\TestCase;
use mako\validator\input\http\routing\traits\InputValidationTrait;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class InputValidationTraitTest extends TestCase
{
	/**
	 *
	 */
	public function testGetValidatedInputWithArray(): void
	{
		$request = Mockery::mock(Request::class);

		$parameters = Mockery::mock(Parameters::class);

		$request->shouldReceive('getData')->once()->andReturn($parameters);

		$parameters->shouldReceive('all')->andReturn([]);

		$class = new class {
			use InputValidationTrait;

			public $request;

			public function test(): array
			{
				return $this->getValidatedInput(['foo' => ['required']]);
			}

			public function baseGetValidatedInput(...$arguments): array
			{
				return $arguments;
			}
		};

		$class->request = $request;

		$this->assertSame([[], ['foo' => ['required']], false], $class->test());
	}

	/**
	 *
	 */
	public function testGetValidatedInputWithString(): void
	{
		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getData')->never();

		$class = new class {
			public $request;

			use InputValidationTrait;

			public function test(): array
			{
				return $this->getValidatedInput('Foo');
			}

			public function baseGetValidatedInput(...$arguments): array
			{
				return $arguments;
			}
		};

		$class->request = $request;

		$this->assertSame(['Foo', 'validateEmptyFields' => false], $class->test());
	}

	/**
	 *
	 */
	public function testGetValidatedFilesWithArray(): void
	{
		$request = Mockery::mock(Request::class);

		$files = Mockery::mock(Files::class);

		(function () use ($files): void {
			$this->files = $files;
		})->bindTo($request, Request::class)();

		$files->shouldReceive('all')->andReturn([]);

		$class = new class {
			use InputValidationTrait;

			public $request;

			public function test(): array
			{
				return $this->getValidatedFiles(['foo' => ['required']]);
			}

			public function baseGetValidatedInput(...$arguments): array
			{
				return $arguments;
			}
		};

		$class->request = $request;

		$this->assertSame([[], ['foo' => ['required']]], $class->test());
	}

	/**
	 *
	 */
	public function testGetValidatedFilesWithString(): void
	{
		$request = Mockery::mock(Request::class);

		$request->shouldReceive('getData')->never();

		$class = new class {
			use InputValidationTrait;

			public $request;

			public function test(): array
			{
				return $this->getValidatedFiles('Foo');
			}

			public function baseGetValidatedInput(...$arguments): array
			{
				return $arguments;
			}
		};

		$class->request = $request;

		$this->assertSame(['Foo'], $class->test());
	}
}
