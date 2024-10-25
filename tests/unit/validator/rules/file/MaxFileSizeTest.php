<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules\file;

use mako\tests\TestCase;
use mako\validator\rules\file\MaxFileSize;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use SplFileInfo;

#[Group('unit')]
class MaxFileSizeTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new MaxFileSize(0);

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testConvertToBytes(): void
	{
		$rule = new class(0) extends MaxFileSize {
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
		$this->assertSame(1024 ** 5, $rule->convert('1PiB'));
		$this->assertSame(1024 ** 6, $rule->convert('1EiB'));
		$this->assertSame(1024 ** 7, $rule->convert('1ZiB'));
		$this->assertSame(1024 ** 8, $rule->convert('1YiB'));
	}

	/**
	 *
	 */
	public function testConvertToBytesWithInvalidUnit(): void
	{
		$this->expectException(RuntimeException::class);

		$this->expectExceptionMessage('Invalid unit type [ Foo ].');

		$rule = new class(0) extends MaxFileSize {
			public function convert($size)
			{
				return $this->convertToBytes($size);
			}
		};

		$this->assertSame(1024 ** 8, $rule->convert('1Foo'));
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new MaxFileSize('1MiB');

		$this->assertTrue($rule->validate(new SplFileInfo(__DIR__ . '/fixtures/png.png'), '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new MaxFileSize('0.5KiB');

		$this->assertFalse($rule->validate(new SplFileInfo(__DIR__ . '/fixtures/png.png'), '', []));

		$this->assertSame('The foobar must be less than 0.5KiB in size.', $rule->getErrorMessage('foobar'));
	}
}
