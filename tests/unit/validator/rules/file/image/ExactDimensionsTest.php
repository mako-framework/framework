<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules\file\image;

use mako\file\FileInfo;
use mako\tests\TestCase;
use mako\validator\rules\file\image\ExactDimensions;
use Mockery;

/**
 * @group unit
 */
class ExactDimensionsTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new ExactDimensions(800, 600);

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		/** @var \mako\file\FileInfo|\Mockery\MockInterface $fileInfo */
		$fileInfo = Mockery::mock(FileInfo::class);

		/** @var \mako\validator\rules\file\image\ExactDimensions|\Mockery\MockInterface $rule */
		$rule = Mockery::mock(ExactDimensions::class, [800, 600]);

		$rule = $rule->shouldAllowMockingProtectedMethods()->makePartial();

		$rule->shouldReceive('getImageSize')->once()->with($fileInfo)->andReturn([800, 600]);

		$this->assertTrue($rule->validate($fileInfo, '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		/** @var \mako\file\FileInfo|\Mockery\MockInterface $fileInfo */
		$fileInfo = Mockery::mock(FileInfo::class);

		/** @var \mako\validator\rules\file\image\ExactDimensions|\Mockery\MockInterface $rule */
		$rule = Mockery::mock(ExactDimensions::class, [800, 600]);

		$rule = $rule->shouldAllowMockingProtectedMethods()->makePartial();

		$rule->shouldReceive('getImageSize')->once()->with($fileInfo)->andReturn([799, 599]);

		$this->assertFalse($rule->validate($fileInfo, '', []));

		$this->assertSame('The foobar does not meet the required dimensions of 800x600 pixels.', $rule->getErrorMessage('foobar'));
	}
}
