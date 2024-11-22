<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\components;

use mako\cli\Environment;
use mako\cli\output\components\Alert;
use mako\cli\output\Output;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class AlertTest extends TestCase
{
	/**
	 *
	 */
	public function testRender(): void
	{
		/** @var Environment|Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('getWidth')->once()->andReturn(15);

		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		(function () use ($env) {
			$this->formatter = null;
			$this->environment = $env;
		})->bindTo($output, Output::class)();

		$alert = new Alert($output);

		$expected = '               '
		. PHP_EOL
		. ' This is just  '
		. PHP_EOL
		. ' a test!       '
		. PHP_EOL
		. '               '
		. PHP_EOL;

		$this->assertSame($expected, $alert->render('This is just a test!'));
	}

	/**
	 *
	 */
	public function testRenderWithWidth(): void
	{
		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getFormatter')->andReturn(null);

		(function () {
			$this->formatter = null;
		})->bindTo($output, Output::class)();

		$alert = new Alert($output, 15);

		$expected = '               '
		. PHP_EOL
		. ' This is just  '
		. PHP_EOL
		. ' a test!       '
		. PHP_EOL
		. '               '
		. PHP_EOL;

		$this->assertSame($expected, $alert->render('This is just a test!'));
	}

	/**
	 *
	 */
	public function testDraw(): void
	{
		$expected  = '               '
		. PHP_EOL
		. ' This is just  '
		. PHP_EOL
		. ' a test!       '
		. PHP_EOL
		. '               '
		. PHP_EOL;

		/** @var Environment|Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('getWidth')->once()->andReturn(15);

		/** @var Mockery\MockInterface|Output $output */
		$output = Mockery::mock(Output::class);

		(function () use ($env) {
			$this->formatter = null;
			$this->environment = $env;
		})->bindTo($output, Output::class)();

		$output->shouldReceive('write')->once()->with($expected, Output::STANDARD);

		$alert = new Alert($output);

		$alert->draw('This is just a test!');
	}
}
