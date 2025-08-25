<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules\file;

use mako\file\FileInfo;
use mako\tests\TestCase;
use mako\validator\rules\file\Hash;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class HashTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new Hash('hash');

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new Hash('hash');

		$fileInfo = Mockery::mock(FileInfo::class);

		$fileInfo->shouldReceive('validateHash')->once()->with('hash', 'sha256')->andReturn(true);

		$this->assertTrue($rule->validate($fileInfo, '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new Hash('hash');

		$fileInfo = Mockery::mock(FileInfo::class);

		$fileInfo->shouldReceive('validateHash')->once()->with('hash', 'sha256')->andReturn(false);

		$this->assertFalse($rule->validate($fileInfo, '', []));

		$this->assertSame('The foobar does not match the expected hash.', $rule->getErrorMessage('foobar'));
	}
}
