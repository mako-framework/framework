<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules\file\image;

use mako\file\FileInfo;
use mako\tests\TestCase;
use mako\validator\rules\file\image\AspectRatio;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class AspectRatioTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new AspectRatio(1, 1);

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		/** @var FileInfo|Mockery\MockInterface $fileInfo */
		$fileInfo = Mockery::mock(FileInfo::class);

		/** @var AspectRatio|Mockery\MockInterface $rule */
		$rule = Mockery::mock(AspectRatio::class, [4, 3]);

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

		/** @var AspectRatio|Mockery\MockInterface $rule */
		$rule = Mockery::mock(AspectRatio::class, [14, 9]);

		$rule = $rule->shouldAllowMockingProtectedMethods()->makePartial();

		$rule->shouldReceive('getImageSize')->once()->with($fileInfo)->andReturn([800, 600]);

		$this->assertFalse($rule->validate($fileInfo, '', []));

		$this->assertSame('The foobar does not have the required aspect ratio of 14:9.', $rule->getErrorMessage('foobar'));
	}
}
