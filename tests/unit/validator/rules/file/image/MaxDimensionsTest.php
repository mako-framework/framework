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
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class MaxDimensionsTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new MaxDimensions(800, 600);

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		/** @var FileInfo|Mockery\MockInterface $fileInfo */
		$fileInfo = Mockery::mock(FileInfo::class);

		/** @var MaxDimensions|Mockery\MockInterface $rule */
		$rule = Mockery::mock(MaxDimensions::class, [800, 600]);

		$rule = $rule->shouldAllowMockingProtectedMethods()->makePartial();

		$rule->shouldReceive('getImageSize')->once()->with($fileInfo)->andReturn([800, 600]);

		$this->assertTrue($rule->validate($fileInfo, '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		/** @var FileInfo|Mockery\MockInterface $fileInfo */
		$fileInfo = Mockery::mock(FileInfo::class);

		/** @var MaxDimensions|Mockery\MockInterface $rule */
		$rule = Mockery::mock(MaxDimensions::class, [800, 600]);

		$rule = $rule->shouldAllowMockingProtectedMethods()->makePartial();

		$rule->shouldReceive('getImageSize')->once()->with($fileInfo)->andReturn([801, 401]);

		$this->assertFalse($rule->validate($fileInfo, '', []));

		$this->assertSame('The foobar exceeds the maximum dimensions of 800x600 pixels.', $rule->getErrorMessage('foobar'));
	}
}
