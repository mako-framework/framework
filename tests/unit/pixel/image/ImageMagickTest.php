<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image;

use mako\pixel\image\exceptions\ImageException;
use mako\pixel\image\ImageMagick;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[Group('unit')]
#[RequiresPhpExtension('imagick')]
class ImageMagickTest extends TestCase
{
	/**
	 *
	 */
	public function testDimensions(): void
	{
		$image = new ImageMagick(__DIR__ . '/fixtures/001.png');

		$this->assertSame(300, $image->getWidth());

		$this->assertSame(300, $image->getHeight());

		$dimensions = $image->getDimensions();

		$this->assertSame(300, $dimensions->width);
		$this->assertSame(300, $dimensions->height);
	}

	/**
	 *
	 */
	public function testToDataUri(): void
	{
		$image = new ImageMagick(__DIR__ . '/fixtures/onebyone.jpg');

		$this->assertStringStartsWith('data:image/jpeg;base64,', $image->toDataUri());
		$this->assertStringStartsWith('data:image/jpeg;base64,', $image->toDataUri('jpg'));
		$this->assertStringStartsWith('data:image/jpeg;base64,', $image->toDataUri('jpeg'));
		$this->assertStringStartsWith('data:image/jpeg;base64,', $image->toDataUri('image/jpg'));
		$this->assertStringStartsWith('data:image/jpeg;base64,', $image->toDataUri('image/jpeg'));
		$this->assertStringStartsWith('data:image/gif;base64,', $image->toDataUri('gif'));
		$this->assertStringStartsWith('data:image/tiff;base64,', $image->toDataUri('tif'));
		$this->assertStringStartsWith('data:image/tiff;base64,', $image->toDataUri('tiff'));
	}

	/**
	 *
	 */
	public function testGetMimeType(): void
	{
		$image = new ImageMagick(__DIR__ . '/fixtures/001.png');

		$this->assertSame('image/png', $image->getMimeType());

		$image = new ImageMagick(__DIR__ . '/fixtures/002.jpg');

		$this->assertSame('image/jpeg', $image->getMimeType());
	}

	/**
	 *
	 */
	public function testFromPath(): void
	{
		$image = ImageMagick::fromPath(__DIR__ . '/fixtures/001.png');

		$this->assertSame('image/png', $image->getMimeType());
	}

	/**
	 *
	 */
	public function testFromBlob(): void
	{
		$image = ImageMagick::fromBlob(file_get_contents(__DIR__ . '/fixtures/001.png'));

		$this->assertSame('image/png', $image->getMimeType());
	}

	/**
	 *
	 */
	public function testSaveFromBlobWithoutPath(): void
	{
		$this->expectException(ImageException::class);
		$this->expectExceptionMessageIs('An image path must be provided when saving images created from a blob.');

		$image = ImageMagick::fromBlob(file_get_contents(__DIR__ . '/fixtures/001.png'));

		$image->save();
	}

	/**
	 *
	 */
	public function testFromStream(): void
	{
		$stream = fopen(__DIR__ . '/fixtures/001.png', 'rb');

		$image = ImageMagick::fromStream($stream);

		$this->assertSame('image/png', $image->getMimeType());

		fclose($stream);
	}
}
