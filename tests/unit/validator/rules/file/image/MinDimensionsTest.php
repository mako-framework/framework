<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules\file\image;

use mako\file\FileInfo;
use mako\tests\TestCase;
use mako\validator\rules\file\image\MinDimensions;
use Mockery;

/**
 * @group unit
 */
class MinDimensionsTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new MinDimensions;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$fileInfo = Mockery::mock(FileInfo::class);

		$rule = Mockery::mock(MinDimensions::class)->shouldAllowMockingProtectedMethods()->makePartial();

		$rule->shouldReceive('getImageSize')->once()->with($fileInfo)->andReturn([800, 600]);

		$rule->setParameters([800, 600]);

		$this->assertTrue($rule->validate($fileInfo, []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$fileInfo = Mockery::mock(FileInfo::class);

		$rule = Mockery::mock(MinDimensions::class)->shouldAllowMockingProtectedMethods()->makePartial();

		$rule->shouldReceive('getImageSize')->once()->with($fileInfo)->andReturn([799, 599]);

		$rule->setParameters([800, 600]);

		$this->assertFalse($rule->validate($fileInfo, []));

		$this->assertSame('The foobar falls short of the minimum dimensions of 800x600 pixels.', $rule->getErrorMessage('foobar'));
	}
}
