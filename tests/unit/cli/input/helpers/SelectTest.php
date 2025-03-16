<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\input\helpers;

use mako\cli\Environment;
use mako\cli\input\helpers\Select;
use mako\cli\input\Input;
use mako\cli\output\Output;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class SelectTest extends TestCase
{
	/**
	 *
	 */
	public function testNonInteractiveSelectAndPickFirstOptionWithNumericInput(): void
	{
		/** @var Input|Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('1');

		/** @var Environment|Mockery\MockInterface $environment */
		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('hasStty')->once()->andReturn(false);

		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		(function () use ($environment): void {
			$this->environment = $environment;
		})->bindTo($output, Output::class)();

		$output->shouldReceive('writeLn')->once()->with('Favorite food?');

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'
		1) Burgers
		2) Sushi
		>
		OUTPUT . ' ');

		$select = new Select($input, $output);

		$this->assertSame(0, $select->ask('Favorite food?', ['Burgers', 'Sushi']));
	}

	/**
	 *
	 */
	public function testSelectAndPickFirstOptionWithNumericInput(): void
	{
		/** @var Input|Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('1');

		/** @var Environment|Mockery\MockInterface $environment */
		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('hasStty')->once()->andReturn(false);

		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		(function () use ($environment): void {
			$this->environment = $environment;
		})->bindTo($output, Output::class)();

		$output->shouldReceive('writeLn')->once()->with('Favorite food?');

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'
		1) Burgers
		2) Sushi
		>
		OUTPUT . ' ');

		$select = new Select($input, $output);

		$this->assertSame(0, $select->ask('Favorite food?', ['Burgers', 'Sushi']));
	}

	/**
	 *
	 */
	public function testSelectAndPickSecondOption(): void
	{
		/** @var Input|Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('2');

		/** @var Environment|Mockery\MockInterface $environment */
		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('hasStty')->once()->andReturn(false);

		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		(function () use ($environment): void {
			$this->environment = $environment;
		})->bindTo($output, Output::class)();

		$output->shouldReceive('writeLn')->once()->with('Favorite food?');

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'
		1) Burgers
		2) Sushi
		>
		OUTPUT . ' ');

		$select = new Select($input, $output);

		$this->assertSame(1, $select->ask('Favorite food?', ['Burgers', 'Sushi']));
	}

	/**
	 *
	 */
	public function testSelectAndPickFirstOptionAfterPickingInvalidOption(): void
	{
		/** @var Input|Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('3');

		$input->shouldReceive('read')->once()->andReturn('1');

		/** @var Environment|Mockery\MockInterface $environment */
		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('hasStty')->once()->andReturn(false);

		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		(function () use ($environment): void {
			$this->environment = $environment;
		})->bindTo($output, Output::class)();

		$output->shouldReceive('writeLn')->once()->with('Favorite food?');

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'
		1) Burgers
		2) Sushi
		>
		OUTPUT . ' ');

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		Invalid choice. Please try again.

		1) Burgers
		2) Sushi
		>
		OUTPUT . ' ');

		$select = new Select($input, $output);

		$this->assertSame(0, $select->ask('Favorite food?', ['Burgers', 'Sushi']));
	}
}
