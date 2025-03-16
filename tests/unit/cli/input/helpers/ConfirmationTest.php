<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\input\helpers;

use mako\cli\Environment;
use mako\cli\input\helpers\Confirmation;
use mako\cli\input\Input;
use mako\cli\output\Output;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class ConfirmationTest extends TestCase
{
	/**
	 *
	 */
	public function testNonInteractiveConfirmationYes(): void
	{
		/** @var Input|Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('y');

		/** @var Environment|Mockery\MockInterface $environment */
		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('hasStty')->once()->andReturn(false);

		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		(function () use ($environment): void {
			$this->environment = $environment;
		})->bindTo($output, Output::class)();

		$output->shouldReceive('writeLn')->once()->with('Delete all files?');

		$output->shouldReceive('write')->once()->with('[yes/NO] > ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(true, $confirmation->ask('Delete all files?'));
	}

	/**
	 *
	 */
	public function testNonInteractiveConfirmationNo(): void
	{
		/** @var Input|Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('no');

		/** @var Environment|Mockery\MockInterface $environment */
		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('hasStty')->once()->andReturn(false);

		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		(function () use ($environment): void {
			$this->environment = $environment;
		})->bindTo($output, Output::class)();

		$output->shouldReceive('writeLn')->once()->with('Delete all files?');

		$output->shouldReceive('write')->once()->with('[yes/NO] > ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(false, $confirmation->ask('Delete all files?'));
	}

	/**
	 *
	 */
	public function testNonInteractiveConfirmationDefaultNo(): void
	{
		/** @var Input|Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn();

		/** @var Environment|Mockery\MockInterface $environment */
		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('hasStty')->once()->andReturn(false);

		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		(function () use ($environment): void {
			$this->environment = $environment;
		})->bindTo($output, Output::class)();

		$output->shouldReceive('writeLn')->once()->with('Delete all files?');

		$output->shouldReceive('write')->once()->with('[yes/NO] > ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(false, $confirmation->ask('Delete all files?', false));
	}

	/**
	 *
	 */
	public function testNonInteractiveConfirmationDefaultYes(): void
	{
		/** @var Input|Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn();

		/** @var Environment|Mockery\MockInterface $environment */
		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('hasStty')->once()->andReturn(false);

		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		(function () use ($environment): void {
			$this->environment = $environment;
		})->bindTo($output, Output::class)();

		$output->shouldReceive('writeLn')->once()->with('Delete all files?');

		$output->shouldReceive('write')->once()->with('[YES/no] > ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(true, $confirmation->ask('Delete all files?', true));
	}

	/**
	 *
	 */
	public function testNonInteractiveConfirmationYesCustom(): void
	{
		/** @var Input|Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('ja');

		/** @var Environment|Mockery\MockInterface $environment */
		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('hasStty')->once()->andReturn(false);

		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		(function () use ($environment): void {
			$this->environment = $environment;
		})->bindTo($output, Output::class)();

		$output->shouldReceive('writeLn')->once()->with('Delete all files?');

		$output->shouldReceive('write')->once()->with('[ja/NEI] > ');

		$confirmation = new Confirmation($input, $output, trueLabel: 'Ja', falseLabel: 'Nei');

		$this->assertSame(true, $confirmation->ask('Delete all files?'));
	}

	/**
	 *
	 */
	public function testNonInteractiveConfirmationWithInvalidInput(): void
	{
		/** @var Input|Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('x');
		$input->shouldReceive('read')->once()->andReturn('y');

		/** @var Environment|Mockery\MockInterface $environment */
		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('hasStty')->once()->andReturn(false);

		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		(function () use ($environment): void {
			$this->environment = $environment;
		})->bindTo($output, Output::class)();

		$output->shouldReceive('writeLn')->once()->with('Delete all files?');

		$output->shouldReceive('write')->times(2)->with('[yes/NO] > ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(true, $confirmation->ask('Delete all files?'));
	}
}
