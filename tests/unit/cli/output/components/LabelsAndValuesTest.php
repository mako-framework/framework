<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\components;

use mako\cli\Environment;
use mako\cli\output\components\LabelsAndValues;
use mako\cli\output\Output;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class LabelsAndValuesTest extends TestCase
{
	/**
	 *
	 */
	public function testRender(): void
	{
		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('getWidth')->once()->andReturn(100);

		$output = Mockery::mock(Output::class);

		(function () use ($environment): void {
			$this->formatter = null;
			$this->environment = $environment;
		})->bindTo($output, Output::class)();

		$labelsAndValues = [
			'Files ok'      => '164,089,973',
			'Files missing' => '1,342,659',
			'Total files'   => '165,432,632',
		];

		$labelsValues = new LabelsAndValues($output);

		$expected  = 'Files ok ............................................................................... 164,089,973' . PHP_EOL;
		$expected .= 'Files missing ............................................................................ 1,342,659' . PHP_EOL;
		$expected .= 'Total files ............................................................................ 165,432,632' . PHP_EOL;

		$this->assertSame($expected, $labelsValues->render($labelsAndValues));
	}

	/**
	 *
	 */
	public function testRenderWith50PctWidth(): void
	{
		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('getWidth')->once()->andReturn(100);

		$output = Mockery::mock(Output::class);

		(function () use ($environment): void {
			$this->formatter = null;
			$this->environment = $environment;
		})->bindTo($output, Output::class)();

		$labelsAndValues = [
			'Files ok'      => '164,089,973',
			'Files missing' => '1,342,659',
			'Total files'   => '165,432,632',
		];

		$labelsValues = new LabelsAndValues($output, 50);

		$expected  = 'Files ok ............................. 164,089,973' . PHP_EOL;
		$expected .= 'Files missing .......................... 1,342,659' . PHP_EOL;
		$expected .= 'Total files .......................... 165,432,632' . PHP_EOL;

		$this->assertSame($expected, $labelsValues->render($labelsAndValues));
	}

	/**
	 *
	 */
	public function testRenderWith50PctWidthWithMaxWidthOf40(): void
	{
		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('getWidth')->once()->andReturn(100);

		$output = Mockery::mock(Output::class);

		(function () use ($environment): void {
			$this->formatter = null;
			$this->environment = $environment;
		})->bindTo($output, Output::class)();

		$labelsAndValues = [
			'Files ok'      => '164,089,973',
			'Files missing' => '1,342,659',
			'Total files'   => '165,432,632',
		];

		$labelsValues = new LabelsAndValues($output, 50, 40);

		$expected  = 'Files ok ................... 164,089,973' . PHP_EOL;
		$expected .= 'Files missing ................ 1,342,659' . PHP_EOL;
		$expected .= 'Total files ................ 165,432,632' . PHP_EOL;

		$this->assertSame($expected, $labelsValues->render($labelsAndValues));
	}

	/**
	 *
	 */
	public function testRenderWithMinSeparators(): void
	{
		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('getWidth')->once()->andReturn(0);

		$output = Mockery::mock(Output::class);

		(function () use ($environment): void {
			$this->formatter = null;
			$this->environment = $environment;
		})->bindTo($output, Output::class)();

		$labelsAndValues = [
			'Files ok'      => '164,089,973',
			'Files missing' => '1,342,659',
			'Total files'   => '165,432,632',
		];

		$labelsValues = new LabelsAndValues($output, minSeparatorCount: 5);

		$expected  = 'Files ok ........ 164,089,973' . PHP_EOL;
		$expected .= 'Files missing ..... 1,342,659' . PHP_EOL;
		$expected .= 'Total files ..... 165,432,632' . PHP_EOL;

		$this->assertSame($expected, $labelsValues->render($labelsAndValues));
	}

	/**
	 *
	 */
	public function testDrawWithMinSeparators(): void
	{
		$environment = Mockery::mock(Environment::class);

		$environment->shouldReceive('getWidth')->once()->andReturn(0);

		$output = Mockery::mock(Output::class);

		(function () use ($environment): void {
			$this->formatter = null;
			$this->environment = $environment;
		})->bindTo($output, Output::class)();

		$labelsAndValues = [
			'Files ok'      => '164,089,973',
			'Files missing' => '1,342,659',
			'Total files'   => '165,432,632',
		];

		$labelsValues = new LabelsAndValues($output, minSeparatorCount: 5);

		$expected  = 'Files ok ........ 164,089,973' . PHP_EOL;
		$expected .= 'Files missing ..... 1,342,659' . PHP_EOL;
		$expected .= 'Total files ..... 165,432,632' . PHP_EOL;

		$output->shouldReceive('write')->once()->with($expected, 1);

		$labelsValues->draw($labelsAndValues);
	}
}
