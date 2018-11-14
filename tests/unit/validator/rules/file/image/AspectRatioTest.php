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
	public function testValidatesWhenEmpty()
	{
		$rule = new AspectRatio;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue()
	{
		$fileInfo = Mockery::mock(FileInfo::class);

		$rule = Mockery::mock(AspectRatio::class)->shouldAllowMockingProtectedMethods()->makePartial();

		$rule->shouldReceive('getImageSize')->once()->with($fileInfo)->andReturn([800, 600]);

		$rule->setParameters([4, 3]);

		$this->assertTrue($rule->validate($fileInfo, []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue()
	{
		$fileInfo = Mockery::mock(FileInfo::class);

		$rule = Mockery::mock(AspectRatio::class)->shouldAllowMockingProtectedMethods()->makePartial();

		$rule->shouldReceive('getImageSize')->once()->with($fileInfo)->andReturn([800, 600]);

		$rule->setParameters([14, 9]);

		$this->assertFalse($rule->validate($fileInfo, []));

		$this->assertSame('The foobar does not have the required aspect ratio of 14:9.', $rule->getErrorMessage('foobar'));
	}
}
