<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\input\helpers;

use mako\cli\input\helpers\Prompt;
use mako\cli\input\Input;
use mako\cli\output\Output;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class PromptTest extends TestCase
{
	/**
	 *
	 */
	public function testPrompt(): void
	{
		/** @var Input|Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('foobar');

		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with('Username:' . PHP_EOL . '> ');

		$question = new Prompt($input, $output);

		$this->assertSame('foobar', $question->ask('Username:'));
	}

	/**
	 *
	 */
	public function testQuestionWithCustomPrompt(): void
	{
		/** @var Input|Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('foobar');

		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with('Username:' . PHP_EOL . '[ ');

		$question = new Prompt($input, $output, '[');

		$this->assertSame('foobar', $question->ask('Username:'));
	}

	/**
	 *
	 */
	public function testQuestionWithNoInputAndNullDefault(): void
	{
		/** @var Input|Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn();

		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with('Username:' . PHP_EOL . '> ');

		$question = new Prompt($input, $output);

		$this->assertSame(null, $question->ask('Username:'));
	}

	/**
	 *
	 */
	public function testQuestionWithNoInputAndCustomDefault(): void
	{
		/** @var Input|Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn();

		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with('Username:' . PHP_EOL . '> ');

		$question = new Prompt($input, $output);

		$this->assertSame('foobar', $question->ask('Username:', 'foobar'));
	}
}
