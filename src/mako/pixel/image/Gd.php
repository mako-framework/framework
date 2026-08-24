<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image;

use mako\pixel\image\exceptions\ImageException;
use mako\pixel\image\geometry\Dimensions;
use Override;

use function fwrite;
use function getimagesize;
use function getimagesizefromstring;
use function imagealphablending;
use function imageavif;
use function imagebmp;
use function imagecolorallocatealpha;
use function imagecopy;
use function imagecreatefromavif;
use function imagecreatefrombmp;
use function imagecreatefromgif;
use function imagecreatefromjpeg;
use function imagecreatefrompng;
use function imagecreatefromstring;
use function imagecreatefromwebp;
use function imagecreatetruecolor;
use function imagefill;
use function imagegif;
use function imagejpeg;
use function imagepng;
use function imagesavealpha;
use function imagesx;
use function imagesy;
use function imagewebp;
use function ob_get_clean;
use function ob_start;
use function pathinfo;
use function round;
use function sprintf;
use function stream_get_contents;
use function strtolower;

/**
 * GD.
 *
 * @see https://www.php.net/manual/en/book.image.php
 *
 * @property ?\GdImage $imageResource
 */
class Gd extends Image
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	final public function __clone()
	{
		$width = imagesx($this->imageResource);
		$height = imagesy($this->imageResource);

		$imageResource = imagecreatetruecolor($width, $height);

		imagealphablending($imageResource, false);
		imagesavealpha($imageResource, true);

		imagecopy($imageResource, $this->imageResource, 0, 0, 0, 0, $width, $height);

		$this->imageResource = $imageResource;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function createImageResource(Dimensions $dimensions, Color $fill): object
	{
		$this->mimeType = 'image/png';

		$imageResource = imagecreatetruecolor($dimensions->width, $dimensions->height);

		$color = imagecolorallocatealpha(
			$imageResource,
			$fill->red,
			$fill->green,
			$fill->blue,
			127 - (int) round($fill->alpha / 255 * 127),
		);

		imagefill($imageResource, 0, 0, $color);

		return $imageResource;
	}

	/**
	 * Returns information about the image.
	 */
	protected function getImageInfoFromPath(string $imagePath): array
	{
		$imageInfo = getimagesize($imagePath);

		if ($imageInfo === false) {
			throw new ImageException(sprintf('Unable to process the image [ %s ].', $imagePath));
		}

		return $imageInfo;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function createImageResourceFromPath(string $imagePath): object
	{
		$this->imagePath = $imagePath;

		$imageInfo = $this->getImageInfoFromPath($imagePath);

		$this->mimeType = $this->normalizeMimeType($imageInfo['mime']);

		return match ($imageInfo[2]) {
			IMAGETYPE_JPEG => imagecreatefromjpeg($imagePath),
			IMAGETYPE_GIF  => imagecreatefromgif($imagePath),
			IMAGETYPE_PNG  => imagecreatefrompng($imagePath),
			IMAGETYPE_WEBP => imagecreatefromwebp($imagePath),
			IMAGETYPE_AVIF => imagecreatefromavif($imagePath),
			IMAGETYPE_BMP  => imagecreatefrombmp($imagePath),
			default        => throw new ImageException(sprintf('Unable to create image resource from [ %s ]. Unsupported image type [ %s ].', $imagePath, $this->mimeType)),
		};
    }

	/**
	 * Returns information about the image.
	 */
	protected function getImageInfoFromBlob(string $blob): array
	{
		$imageInfo = getimagesizefromstring($blob);

		if ($imageInfo === false) {
			throw new ImageException('Unable to process the image.');
		}

		return $imageInfo;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function createImageResourceFromBlob(string $blob): object
	{
		$imageInfo = $this->getImageInfoFromBlob($blob);

		$this->mimeType = $this->normalizeMimeType($imageInfo['mime']);

		return imagecreatefromstring($blob);
    }

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function createImageResourceFromStream(mixed $stream): object
	{
		return $this->createImageResourceFromBlob(stream_get_contents($stream));
    }

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function destroyImageResource(): void
	{
		$this->imageResource = null;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function getImageResourceAsBlob(?string $type, int $quality): string
	{
		$type ??= $this->mimeType;

		ob_start();

		switch (strtolower($type)) {
			case 'gif':
			case 'image/gif':
				imagegif($this->imageResource);
				break;
			case 'jpg':
			case 'jpeg':
			case 'image/jpg':
			case 'image/jpeg':
				imagejpeg($this->imageResource, quality: $quality);
				break;
			case 'png':
			case 'image/png':
				imagealphablending($this->imageResource, true);
				imagesavealpha($this->imageResource, true);
				imagepng($this->imageResource, quality: (int) (9 - (round(($quality / 100) * 9))));
				break;
			case 'webp':
			case 'image/webp':
				imagewebp($this->imageResource, quality: $quality);
				break;
			case 'avif':
			case 'image/avif':
				imageavif($this->imageResource, quality: $quality);
				break;
			case 'bmp':
			case 'image/bmp':
				imagebmp($this->imageResource);
				break;
			default:
				throw new ImageException(sprintf('Unsupported image type [ %s ].', $type));
		}

		return ob_get_clean();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function writeImageResourceToStream(mixed $stream, ?string $type = null, int $quality = 95): void
	{
		fwrite($stream, $this->getImageResourceAsBlob($type, $quality));
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function saveImageResource(string $imagePath, int $quality): void
	{
		$extension = pathinfo($imagePath, PATHINFO_EXTENSION);

		switch (strtolower($extension)) {
			case 'gif':
				imagegif($this->imageResource, $imagePath);
				break;
			case 'jpg':
			case 'jpeg':
				imagejpeg($this->imageResource, $imagePath, $quality);
				break;
			case 'png':
				imagealphablending($this->imageResource, true);
				imagesavealpha($this->imageResource, true);
				imagepng($this->imageResource, $imagePath, (int) (9 - (round(($quality / 100) * 9))));
				break;
			case 'webp':
				imagewebp($this->imageResource, $imagePath, $quality);
				break;
			case 'avif':
				imageavif($this->imageResource, $imagePath, $quality);
				break;
			case 'bmp':
				imagebmp($this->imageResource, $imagePath);
				break;
			default:
				throw new ImageException(sprintf('Unable to save as [ %s ]. Unsupported image format.', $extension));
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getWidth(): int
	{
		return imagesx($this->imageResource);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getHeight(): int
	{
		return imagesy($this->imageResource);
	}
}
