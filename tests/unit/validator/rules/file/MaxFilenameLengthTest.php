<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules\file;

use mako\file\FileInfo;
use mako\http\request\UploadedFile;
use mako\tests\TestCase;
use mako\validator\rules\file\MaxFilenameLength;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class MaxFilenameLengthTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new MaxFilenameLength(10);

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new MaxFilenameLength(10);

		$fileInfo = Mockery::mock(FileInfo::class);

		$fileInfo->shouldReceive('getFilename')->once()->andReturn('foo.txt');

		$this->assertTrue($rule->validate($fileInfo, '', []));

		//

		$rule = new MaxFilenameLength(10);

		$fileInfo = Mockery::mock(UploadedFile::class);

		$fileInfo->shouldReceive('getReportedFilename')->once()->andReturn('foo.txt');

		$this->assertTrue($rule->validate($fileInfo, '', []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new MaxFilenameLength(5);

		$fileInfo = Mockery::mock(FileInfo::class);

		$fileInfo->shouldReceive('getFilename')->once()->andReturn('foo.txt');

		$this->assertFalse($rule->validate($fileInfo, '', []));

		$this->assertSame('The foobar filename must be at most 5 characters long.', $rule->getErrorMessage('foobar'));

		//

		$rule = new MaxFilenameLength(5);

		$fileInfo = Mockery::mock(UploadedFile::class);

		$fileInfo->shouldReceive('getReportedFilename')->once()->andReturn('foo.txt');

		$this->assertFalse($rule->validate($fileInfo, '', []));

		$this->assertSame('The foobar filename must be at most 5 characters long.', $rule->getErrorMessage('foobar'));
	}
}
