<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\input\helpers;

use mako\cli\Environment;
use mako\cli\input\helpers\Select;
use mako\cli\input\helpers\select\AsciiTheme;
use mako\cli\input\helpers\select\Theme;
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
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('1');

		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('hasStty')->once()->andReturn(false);

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
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('2');

		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('hasStty')->once()->andReturn(false);

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
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('1,2');

		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('hasStty')->once()->andReturn(false);

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

		$select = new Select($input, $output, allowMultiple: true);

		$this->assertSame([0, 1], $select->ask(
			'Favorite food?',
			['Burgers', 'Sushi']
		));
	}

	/**
	 *
	 */
	public function testNonInteractiveSelectAndPickMultipleOptionsAndReturnValues(): void
	{
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('1,2');

		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('hasStty')->once()->andReturn(false);

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

		$select = new Select($input, $output, returnKey: false, allowMultiple: true);

		$this->assertSame(['Burgers', 'Sushi'], $select->ask(
			'Favorite food?',
			['Burgers', 'Sushi']
		));
	}

	/**
	 *
	 */
	public function testNonInteractiveSelectAndPickFirstOptionAfterPickingInvalidOption(): void
	{
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('3');

		$input->shouldReceive('read')->once()->andReturn('1');

		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('hasStty')->once()->andReturn(false);

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
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('');

		$input->shouldReceive('read')->once()->andReturn('1');

		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('hasStty')->once()->andReturn(false);

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
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('');

		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('hasStty')->once()->andReturn(false);

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

		$select = new Select($input, $output, allowEmptySelection: true);

		$this->assertSame(null, $select->ask(
			'Favorite food?',
			['Burgers', 'Sushi']
		));
	}

	/**
	 * @return array{
	 * 	Input&Mockery\MockInterface,
	 *	Environment&Mockery\MockInterface,
	 *  Cursor&Mockery\MockInterface,
	 * 	Mockery\MockInterface&Output,
	 * 	Mockery\MockInterface&Select
	 * }
	 */
	protected function getInteractiveMocks(
		string $invalidChoiceMessage = 'Invalid choice. Please try again.',
		string $choiceRequiredMessage = 'You need to make a selection.',
		Theme $theme = new Theme,
		bool $returnKey = true,
		bool $allowMultiple = false,
		bool $allowEmptySelection = false
	): array {
		$input = Mockery::mock(Input::class);

		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('hasStty')->once()->andReturn(true);
		$environment->shouldReceive('hasAnsiSupport')->once()->andReturn(true);

		$cursor = Mockery::mock(Cursor::class);

		$cursor->shouldReceive('hide');
		$cursor->shouldReceive('show');
		$cursor->shouldReceive('clearScreenFromCursor');
		$cursor->shouldReceive('up');

		$output = Mockery::mock(Output::class);

		(function () use ($environment, $cursor): void {
			$this->environment = $environment;
			$this->cursor = $cursor;
		})->bindTo($output, Output::class)();

		$select = Mockery::mock(Select::class, [
			$input,
			$output,
			$invalidChoiceMessage,
			$choiceRequiredMessage,
			$theme,
			$returnKey,
			$allowMultiple,
			$allowEmptySelection,
		]);

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
	public function testInteractiveSelectAndPickFirstOptionWithAsciiTheme(): void
	{
		[$input, , , $output, $select] = $this->getInteractiveMocks(theme: new AsciiTheme);

		$output->shouldReceive('writeLn')->once()->with('Favorite food?');

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		> [ ] Burgers
		  [ ] Sushi

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::RIGHT->value);

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		> [X] Burgers
		  [ ] Sushi

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
	public function testInteractiveSelectAndPickFirstOptionWithAsciiThemeAndCustomOptionFormatter(): void
	{
		[$input, , , $output, $select] = $this->getInteractiveMocks(theme: new AsciiTheme);

		$output->shouldReceive('writeLn')->once()->with('Favorite food?');

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		> [ ] BURGERS
		  [ ] SUSHI

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::RIGHT->value);

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		> [X] BURGERS
		  [ ] SUSHI

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::ENTER->value);

		$this->assertSame(0, $select->ask(
			'Favorite food?',
			['Burgers', 'Sushi'],
			fn (mixed $option): string => strtoupper($option)
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
		[$input, , , $output, $select] = $this->getInteractiveMocks(allowMultiple: true);

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
			['Burgers', 'Sushi']
		));
	}

	/**
	 *
	 */
	public function testInteractiveSelectAndPickMultipleOptionsAndReturnValues(): void
	{
		[$input, , , $output, $select] = $this->getInteractiveMocks(returnKey: false, allowMultiple: true);

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
			['Burgers', 'Sushi']
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
	public function testInteractiveSelectAndNoOption(): void
	{
		[$input, , , $output, $select] = $this->getInteractiveMocks(allowEmptySelection: true);

		$output->shouldReceive('writeLn')->once()->with('Favorite food?');

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		> ○ Burgers
		  ○ Sushi

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::ENTER->value);

		$this->assertSame(null, $select->ask(
			'Favorite food?',
			['Burgers', 'Sushi']
		));
	}
}
