<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\input\helpers;

use mako\cli\Environment;
use mako\cli\input\helpers\Confirmation;
use mako\cli\input\helpers\confirmation\AsciiTheme;
use mako\cli\input\helpers\confirmation\Theme;
use mako\cli\input\Input;
use mako\cli\input\Key;
use mako\cli\output\Cursor;
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

		$this->assertTrue($confirmation->ask('Delete all files?'));
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

		$this->assertFalse($confirmation->ask('Delete all files?'));
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

		$this->assertFalse($confirmation->ask('Delete all files?', false));
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

		$this->assertTrue($confirmation->ask('Delete all files?', true));
	}

	/**
	 *
	 */
	public function testNonInteractiveConfirmationYesWithCustomLabels(): void
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

		$this->assertTrue($confirmation->ask('Delete all files?'));
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

		$this->assertTrue($confirmation->ask('Delete all files?'));
	}

	/**
	 * @return array{
	 * 	Mockery\MockInterface|Input,
	 *	Mockery\MockInterface|Environment,
	 *  Mockery\MockInterface|Cursor,
	 * 	Mockery\MockInterface|Output,
	 * 	Mockery\MockInterface|Confirmation
	 * }
	 */
	protected function getInteractiveMocks(
		string $trueLabel = 'Yes',
		string $falseLabel = 'No',
		Theme $theme = new Theme,
	): array {
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

		/** @var Confirmation|Mockery\MockInterface $confirmation */
		$confirmation = Mockery::mock(Confirmation::class, [
			$input,
			$output,
			$trueLabel,
			$falseLabel,
			$theme,
		]);

		$confirmation->makePartial();

		$confirmation->shouldAllowMockingProtectedMethods();

		$confirmation->shouldReceive('getSttySettings');
		$confirmation->shouldReceive('setSttySettings');

		return [$input, $environment, $cursor, $output, $confirmation];
	}

	/**
	 *
	 */
	public function testInteractiveConfirmationYes(): void
	{
		[$input, , , $output, $confirmation] = $this->getInteractiveMocks();

		$output->shouldReceive('writeLn')->once()->with('Delete all files?');

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		 ○ Yes ● No

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::LEFT->value);

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		 ● Yes ○ No

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::ENTER->value);

		$this->assertTrue($confirmation->ask('Delete all files?'));
	}

	/**
	 *
	 */
	public function testInteractiveConfirmationNo(): void
	{
		[$input, , , $output, $confirmation] = $this->getInteractiveMocks();

		$output->shouldReceive('writeLn')->once()->with('Delete all files?');

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		 ○ Yes ● No

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::ENTER->value);

		$this->assertFalse($confirmation->ask('Delete all files?'));
	}

	/**
	 *
	 */
	public function testInteractiveConfirmationDefaultNo(): void
	{
		[$input, , , $output, $confirmation] = $this->getInteractiveMocks();

		$output->shouldReceive('writeLn')->once()->with('Delete all files?');

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		 ○ Yes ● No

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::ENTER->value);

		$this->assertFalse($confirmation->ask('Delete all files?', false));
	}

	/**
	 *
	 */
	public function testInteractiveConfirmationDefaulYes(): void
	{
		[$input, , , $output, $confirmation] = $this->getInteractiveMocks();

		$output->shouldReceive('writeLn')->once()->with('Delete all files?');

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		 ● Yes ○ No

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::ENTER->value);

		$this->assertTrue($confirmation->ask('Delete all files?', true));
	}

	/**
	 *
	 */
	public function testInteractiveConfirmationYesWithCustomLabels(): void
	{
		[$input, , , $output, $confirmation] = $this->getInteractiveMocks(
			trueLabel: 'Ja',
			falseLabel: 'Nei'
		);

		$output->shouldReceive('writeLn')->once()->with('Delete all files?');

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		 ○ Ja ● Nei

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::LEFT->value);

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		 ● Ja ○ Nei

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::ENTER->value);

		$this->assertTrue($confirmation->ask('Delete all files?'));
	}

	/**
	 *
	 */
	public function testInteractiveConfirmationYesWithCustomTheme(): void
	{
		[$input, , , $output, $confirmation] = $this->getInteractiveMocks(
			theme: new AsciiTheme
		);

		$output->shouldReceive('writeLn')->once()->with('Delete all files?');

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		 [ ] Yes [X] No

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::LEFT->value);

		$output->shouldReceive('write')->once()->with(<<<'OUTPUT'

		 [X] Yes [ ] No

		OUTPUT);

		$input->shouldReceive('readBytes')->once()->andReturn(Key::ENTER->value);

		$this->assertTrue($confirmation->ask('Delete all files?'));
	}
}
