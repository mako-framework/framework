<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules\file;

use mako\http\request\UploadedFile;
use mako\tests\TestCase;
use mako\validator\rules\file\IsUploaded;
use Mockery;

/**
 * @group unit
 */
class IsUploadedTest extends TestCase
{
	/**
	 *
	 */
	public function testValidatesWhenEmpty()
	{
		$rule = new IsUploaded;

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue()
	{
		$rule = new IsUploaded;

		$uploadedFile = Mockery::mock(UploadedFile::class);

		$uploadedFile->shouldReceive('isUploaded')->once()->andReturnTrue();

		$this->assertTrue($rule->validate($uploadedFile, []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue()
	{
		$rule = new IsUploaded;

		$this->assertFalse($rule->validate(false, []));

		//

		$rule = new IsUploaded;

		$uploadedFile = Mockery::mock(UploadedFile::class);

		$uploadedFile->shouldReceive('isUploaded')->once()->andReturnFalse();

		$this->assertFalse($rule->validate($uploadedFile, []));

		$this->assertSame('The foobar did not successfully upload.', $rule->getErrorMessage('foobar'));
	}
}
