<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\input\helpers;

use mako\cli\Environment;
use mako\cli\input\helpers\Select;
use mako\cli\input\Input;
use mako\cli\input\Key;
use mako\cli\output\Cursor;
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

		$this->assertSame(0, $select->ask(
			'Favorite food?',
			['Burgers', 'Sushi']
		));
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

		$this->assertSame(1, $select->ask(
			'Favorite food?',
			['Burgers', 'Sushi']
		));
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

		$this->assertSame(0, $select->ask(
			'Favorite food?',
			['Burgers', 'Sushi']
		));
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

		$this->assertSame(0, $select->ask(
			'Favorite food?',
			['Burgers', 'Sushi']
		));
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

	/**
	 * @return array{
	 * 	Mockery\MockInterface|Input,
	 *	Mockery\MockInterface|Environment,
	 *  Mockery\MockInterface|Cursor,
	 * 	Mockery\MockInterface|Output,
	 * 	Mockery\MockInterface|Select
	 * }
	 */
	protected function getInteractiveMocks(): array
	{
		/** @var Input|Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		/** @var Environment|Mockery\MockInterface $environment */
		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('hasStty')->once()->andReturn(true);

		/** @var Cursor|Mockery\MockInterface $cursor */
		$cursor = Mockery::mock(Cursor::class);

		$cursor->shouldReceive('hide');
		$cursor->shouldReceive('show');
		$cursor->shouldReceive('clearScreenFromCursor');
		$cursor->shouldReceive('up');

		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		(function () use ($environment, $cursor): void {
			$this->environment = $environment;
			$this->cursor = $cursor;
		})->bindTo($output, Output::class)();

		/** @var Mockery\MockInterface|Select $select */
		$select = Mockery::mock(Select::class, [$input, $output]);

		$select->makePartial();

		$select->shouldAllowMockingProtectedMethods();

		$select->shouldReceive('getSttySettings');
		$select->shouldReceive('setSttySettings');

		return [$input, $environment, $cursor, $output, $select];
	}

	/**
	 *
	 */
	public function testInteractiveSelectAndPickFirstOption(): void
	{
		[$input, , , $output, $select] = $this->getInteractiveMocks();

		$output->shouldReceive('writeLn')->once()->with('Favorite food?');

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		> ○ Burgers
		  ○ Sushi

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::RIGHT->value);

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		> ● Burgers
		  ○ Sushi

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::ENTER->value);

		$this->assertSame(0, $select->ask(
			'Favorite food?',
			['Burgers', 'Sushi']
		));
	}

	/**
	 *
	 */
	public function testInteractiveSelectAndPickSecondOption(): void
	{
		[$input, , , $output, $select] = $this->getInteractiveMocks();

		$output->shouldReceive('writeLn')->once()->with('Favorite food?');

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		> ○ Burgers
		  ○ Sushi

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::DOWN->value);

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		  ○ Burgers
		> ○ Sushi

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::RIGHT->value);

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		  ○ Burgers
		> ● Sushi

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::ENTER->value);

		$this->assertSame(1, $select->ask(
			'Favorite food?',
			['Burgers', 'Sushi']
		));
	}

	/**
	 *
	 */
	public function testInteractiveSelectAndPickMultipleOptions(): void
	{
		[$input, , , $output, $select] = $this->getInteractiveMocks();

		$output->shouldReceive('writeLn')->once()->with('Favorite food?');

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		> ○ Burgers
		  ○ Sushi

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::SPACE->value);

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		> ● Burgers
		  ○ Sushi

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::UP->value);

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		  ● Burgers
		> ○ Sushi

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::SPACE->value);

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		  ● Burgers
		> ● Sushi

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::ENTER->value);

		$this->assertSame([0, 1], $select->ask(
			'Favorite food?',
			['Burgers', 'Sushi'],
			allowMultiple: true
		));
	}

	/**
	 *
	 */
	public function testInteractiveSelectAndPickMultipleOptionsAndReturnValues(): void
	{
		[$input, , , $output, $select] = $this->getInteractiveMocks();

		$output->shouldReceive('writeLn')->once()->with('Favorite food?');

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		> ○ Burgers
		  ○ Sushi

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::SPACE->value);

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		> ● Burgers
		  ○ Sushi

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::UP->value);

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		  ● Burgers
		> ○ Sushi

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::SPACE->value);

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		  ● Burgers
		> ● Sushi

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::ENTER->value);

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
	public function testInteractiveSelectAndPickFirstOptionAfterPickingNoOption(): void
	{
		[$input, , , $output, $select] = $this->getInteractiveMocks();

		$output->shouldReceive('writeLn')->once()->with('Favorite food?');

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		> ○ Burgers
		  ○ Sushi

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::ENTER->value);

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		> ○ Burgers
		  ○ Sushi

		You need to make a selection.

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::LEFT->value);

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		> ● Burgers
		  ○ Sushi

		You need to make a selection.

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::ENTER->value);

		$this->assertSame(0, $select->ask(
			'Favorite food?',
			['Burgers', 'Sushi'],
		));
	}

	/**
	 *
	 */
	public function testInteractiveSelectAndNoOption(): void
	{
		[$input, , , $output, $select] = $this->getInteractiveMocks();

		$output->shouldReceive('writeLn')->once()->with('Favorite food?');

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		> ○ Burgers
		  ○ Sushi

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::ENTER->value);

		$this->assertSame(null, $select->ask(
			'Favorite food?',
			['Burgers', 'Sushi'],
			allowEmptySelection: true
		));
	}
}
