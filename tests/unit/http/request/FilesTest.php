<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\request;

use mako\http\request\Files;
use mako\http\request\UploadedFile;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class FilesTest extends TestCase
{
	/**
	 *
	 */
	protected function getSingleUpload(): array
	{
		return
		[
			'upload' => [
				'name'     => 'foo',
				'tmp_name' => '/tmp/qwerty',
				'type'     => 'foo/bar',
				'size'     => 123,
				'error'    => 0,
			],
		];
	}

	/**
	 *
	 */
	protected function getMultiUpload()
	{
		return
		[
			'upload' => [
				'name'     => ['foo', 'bar'],
				'tmp_name' => ['/tmp/qwerty', '/tmp/azerty'],
				'type'     => ['foo/bar', 'foo/bar'],
				'size'     => [123, 456],
				'error'    => [0, 0],
			],
		];
	}

	/**
	 *
	 */
	public function testCountSet(): void
	{
		$files = new Files($this->getSingleUpload());

		$this->assertSame(1, count($files));

		$files = new Files($this->getMultiUpload());

		$this->assertSame(1, count($files));
	}

	/**
	 *
	 */
	public function testAdd(): void
	{
		$files = new Files;

		$files->add('upload', $this->getSingleUpload()['upload']);

		$this->assertInstanceOf(UploadedFile::class, $files->get('upload'));
	}

	/**
	 *
	 */
	public function testGet(): void
	{
		$files = new Files($this->getSingleUpload());

		$this->assertInstanceOf(UploadedFile::class, $files->get('upload'));

		//

		$files = new Files($this->getMultiUpload());

		$this->assertInstanceOf(UploadedFile::class, $files->get('upload.0'));

		$this->assertInstanceOf(UploadedFile::class, $files->get('upload.1'));
	}
}
