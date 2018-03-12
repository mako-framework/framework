<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules\file;

use mako\tests\TestCase;
use mako\validator\rules\file\MaxFilesize;
use SplFileInfo;

/**
 * @group unit
 */
class MaxFilesizeTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty()
	{
		$rule = new MaxFilesize;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testConvertToBytes()
	{
		$rule = new class extends MaxFilesize
		{
			public function convert($size)
			{
				return $this->convertToBytes($size);
			}
		};

		$this->assertSame(1024, $rule->convert(1024));
		$this->assertSame(1024, $rule->convert('1KiB'));
		$this->assertSame(1024 ** 2, $rule->convert('1MiB'));
		$this->assertSame(1024 ** 3, $rule->convert('1GiB'));
		$this->assertSame(1024 ** 4, $rule->convert('1TiB'));
	}

	/**
	 *
	 */
	public function testWithValidValue()
	{
		$rule = new MaxFilesize;

		$rule->setParameters(['1MiB']);

		$this->assertTrue($rule->validate(new SplFileInfo(__DIR__ . '/fixtures/png.png'), []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue()
	{
		$rule = new MaxFilesize;

		$rule->setParameters(['0.5KiB']);

		$this->assertFalse($rule->validate(new SplFileInfo(__DIR__ . '/fixtures/png.png'), []));

		$this->assertSame('The foobar must be less than 0.5KiB in size.', $rule->getErrorMessage('foobar'));
	}
}
