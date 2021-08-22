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

/**
 * @group unit
 */
class AspectRatioTest extends TestCase
{
	/**
	 *
	 */
	public function setUp(): void
	{
		if(PHP_VERSION_ID >= 80100)
		{
			$this->markTestSkipped('Mockery must be updated to support PHP 8.1.');
		}
	}

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
		$fileInfo = Mockery::mock(FileInfo::class);

		$rule = Mockery::mock(AspectRatio::class, [4, 3])->shouldAllowMockingProtectedMethods()->makePartial();

		$rule->shouldReceive('getImageSize')->once()->with($fileInfo)->andReturn([800, 600]);

		$this->assertTrue($rule->validate($fileInfo, []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$fileInfo = Mockery::mock(FileInfo::class);

		$rule = Mockery::mock(AspectRatio::class, [14, 9])->shouldAllowMockingProtectedMethods()->makePartial();

		$rule->shouldReceive('getImageSize')->once()->with($fileInfo)->andReturn([800, 600]);

		$this->assertFalse($rule->validate($fileInfo, []));

		$this->assertSame('The foobar does not have the required aspect ratio of 14:9.', $rule->getErrorMessage('foobar'));
	}
}
