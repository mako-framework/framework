<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules\file\image;

use mako\file\FileInfo;
use mako\tests\TestCase;
use mako\validator\rules\file\image\MaxDimensions;
use Mockery;

/**
 * @group unit
 */
class MaxDimensionsTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new MaxDimensions;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$fileInfo = Mockery::mock(FileInfo::class);

		$rule = Mockery::mock(MaxDimensions::class)->shouldAllowMockingProtectedMethods()->makePartial();

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

		$rule = Mockery::mock(MaxDimensions::class)->shouldAllowMockingProtectedMethods()->makePartial();

		$rule->shouldReceive('getImageSize')->once()->with($fileInfo)->andReturn([801, 401]);

		$rule->setParameters([800, 600]);

		$this->assertFalse($rule->validate($fileInfo, []));

		$this->assertSame('The foobar exceeds the maximum dimensions of 800x600 pixels.', $rule->getErrorMessage('foobar'));
	}
}
