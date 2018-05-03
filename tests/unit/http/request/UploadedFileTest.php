<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\request;

use mako\http\request\UploadedFile;
use mako\tests\TestCase;

/**
 * @group unit
 */
class UploadedFileTest extends TestCase
{
	/**
	 *
	 */
	public function testGetPath()
	{
		$file = new UploadedFile(__FILE__, 'foo.bar', 123, 'foo/bar', 0);

		$this->assertEquals(__FILE__, $file->getPathname());
	}

	/**
	 *
	 */
	public function testGetName()
	{
		$file = new UploadedFile(__FILE__, 'foo.bar', 123, 'foo/bar', 0);

		$this->assertEquals('foo.bar', $file->getName());
	}

	/**
	 *
	 */
	public function testGetReportedSize()
	{
		$file = new UploadedFile(__FILE__, 'foo.bar', 123, 'foo/bar', 0);

		$this->assertEquals(123, $file->getReportedSize());
	}

	/**
	 *
	 */
	public function testGetReportedType()
	{
		$file = new UploadedFile(__FILE__, 'foo.bar', 123, 'foo/bar', 0);

		$this->assertEquals('foo/bar', $file->getReportedType());
	}

	/**
	 *
	 */
	public function testHasError()
	{
		$file = new UploadedFile(__FILE__, 'foo.bar', 123, 'foo/bar', 0);

		$this->assertFalse($file->hasError());

		$file = new UploadedFile(__FILE__, 'foo.bar', 123, 'foo/bar', 1);

		$this->assertTrue($file->hasError());
	}

	/**
	 *
	 */
	public function testGetErrorCode()
	{
		$file = new UploadedFile(__FILE__, 'foo.bar', 123, 'foo/bar', 0);

		$this->assertEquals(0, $file->getErrorCode());

		$file = new UploadedFile(__FILE__, 'foo.bar', 123, 'foo/bar', 1);

		$this->assertEquals(1, $file->getErrorCode());
	}

	/**
	 *
	 */
	public function testGetErrorMessage()
	{
		$file = new UploadedFile(__FILE__, 'foo.bar', 123, 'foo/bar', UPLOAD_ERR_OK);

		$this->assertEquals('There is no error, the file was successfully uploaded.', $file->getErrorMessage());

		$file = new UploadedFile(__FILE__, 'foo.bar', 123, 'foo/bar', UPLOAD_ERR_INI_SIZE);

		$this->assertEquals('The uploaded file exceeds the upload_max_filesize directive in php.ini.', $file->getErrorMessage());

		$file = new UploadedFile(__FILE__, 'foo.bar', 123, 'foo/bar', UPLOAD_ERR_FORM_SIZE);

		$this->assertEquals('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.', $file->getErrorMessage());

		$file = new UploadedFile(__FILE__, 'foo.bar', 123, 'foo/bar', UPLOAD_ERR_PARTIAL);

		$this->assertEquals('The uploaded file was only partially uploaded.', $file->getErrorMessage());

		$file = new UploadedFile(__FILE__, 'foo.bar', 123, 'foo/bar', UPLOAD_ERR_NO_FILE);

		$this->assertEquals('No file was uploaded.', $file->getErrorMessage());

		$file = new UploadedFile(__FILE__, 'foo.bar', 123, 'foo/bar', UPLOAD_ERR_NO_TMP_DIR);

		$this->assertEquals('Missing a temporary folder.', $file->getErrorMessage());

		$file = new UploadedFile(__FILE__, 'foo.bar', 123, 'foo/bar', UPLOAD_ERR_CANT_WRITE);

		$this->assertEquals('Failed to write file to disk.', $file->getErrorMessage());

		$file = new UploadedFile(__FILE__, 'foo.bar', 123, 'foo/bar', UPLOAD_ERR_EXTENSION);

		$this->assertEquals('A PHP extension stopped the file upload.', $file->getErrorMessage());

		$file = new UploadedFile(__FILE__, 'foo.bar', 123, 'foo/bar', 1000);

		$this->assertEquals('Unknown upload error.', $file->getErrorMessage());
	}

	/**
	 *
	 */
	public function testIsUploadedFile()
	{
		$file = new UploadedFile(__FILE__, 'foo.bar', 123, 'foo/bar', 0);

		$this->assertFalse($file->isUploaded());
	}

	/**
	 * @expectedException \mako\http\request\exceptions\UploadException
	 * @expectedExceptionMessage The uploaded file exceeds the upload_max_filesize directive in php.ini.
	 */
	public function testMoveToWithError()
	{
		$file = new UploadedFile(__FILE__, 'foo.bar', 123, 'foo/bar', UPLOAD_ERR_INI_SIZE);

		$file->moveTo(__FILE__);
	}

	/**
	 * @expectedException \mako\http\request\exceptions\UploadException
	 * @expectedExceptionMessage The file that you're trying to move was not uploaded.
	 */
	public function testMoveToWithNonUploadedFile()
	{
		$file = new UploadedFile(__FILE__, 'foo.bar', 123, 'foo/bar', 0);

		$file->moveTo(__FILE__);
	}

	/*public function testMoveToWithoutError()
	{
		$file = Mockery::mock('mako\http\UploadedFile[isUploaded|moveUploadedFile]', [__FILE__, 'foo.bar', 123, 'foo/bar', 0]);

		$file->shouldAllowMockingProtectedMethods();

		$file->shouldReceive('isUploaded')->once()->andReturn(true);

		$file->shouldReceive('moveUploadedFile')->once()->with(__FILE__)->andReturn(true);

		$this->assertTrue($file->moveTo(__FILE__));
	}*/
}
