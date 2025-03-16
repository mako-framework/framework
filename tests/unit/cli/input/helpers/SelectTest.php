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
	public function testNonInteractiveSelectAndPickFirstOption(): void
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
	public function testNonInteractiveSelectAndPickSecondOption(): void
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
	public function testNonInteractiveSelectAndPickMultipleOptions(): void
	{
		/** @var Input|Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('1,2');

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

		$this->assertSame([0, 1], $select->ask(
			'Favorite food?',
			['Burgers', 'Sushi'],
			allowMultiple: true
		));
	}

	/**
	 *
	 */
	public function testNonInteractiveSelectAndPickMultipleOptionsAndReturnValues(): void
	{
		/** @var Input|Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('1,2');

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

		$this->assertSame(['Burgers', 'Sushi'], $select->ask(
			'Favorite food?',
			['Burgers', 'Sushi'],
			returnKey: false,
			allowMultiple: true
		));
	}

	/**
	 *
	 */
	public function testNonInteractiveSelectAndPickFirstOptionAfterPickingInvalidOption(): void
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

	/**
	 *
	 */
	public function testNonInteractiveSelectAndPickFirstOptionAfterPickingNoOption(): void
	{
		/** @var Input|Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('');

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

		You need to make a selection.

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
	public function testNonInteractiveSelectAndNoOption(): void
	{
		/** @var Input|Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('');

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

		$this->assertSame(null, $select->ask(
			'Favorite food?',
			['Burgers', 'Sushi'],
			allowEmptySelection: true
		));
	}
}
