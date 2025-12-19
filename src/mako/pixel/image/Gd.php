<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image;

use mako\pixel\image\exceptions\ImageException;
use Override;

use function getimagesize;
use function imagealphablending;
use function imagecolorallocatealpha;
use function imagecolortransparent;
use function imagecopy;
use function imagecopyresampled;
use function imagecreatefromgif;
use function imagecreatefromjpeg;
use function imagecreatefrompng;
use function imagecreatetruecolor;
use function imagefill;
use function imagegif;
use function imagejpeg;
use function imagepng;
use function imagerotate;
use function imagesavealpha;
use function imagesx;
use function imagesy;
use function ob_get_clean;
use function ob_start;
use function pathinfo;
use function round;
use function sprintf;
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
	 * Image info.
	 */
	protected ?array $imageInfo = null;

	/**
	 * Returns information about the image.
	 */
	protected function getImageInfo(string $imagePath): array
	{
		$imageInfo = getimagesize($imagePath);

		if ($imageInfo === false) {
			throw new ImageException(sprintf('Unable to process the image [ %s ].', $imagePath));
		}

		return $imageInfo;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @todo add support for more image formats like WebP?
	 */
	#[Override]
    protected function createImageResource(string $imagePath): object
    {
		$imageInfo = $this->getImageInfo($imagePath);

		if ($this->imageInfo === null) {
			$this->imageInfo = $imageInfo;
		}

		return match ($imageInfo[2]) {
			IMAGETYPE_JPEG => imagecreatefromjpeg($imagePath),
			IMAGETYPE_GIF  => imagecreatefromgif($imagePath),
			IMAGETYPE_PNG  => imagecreatefrompng($imagePath),
			default        => throw new ImageException(sprintf('Unable to create image resource from [ %s ]. Unsupported image type.', $imagePath)),
		};
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
	 *
	 * @todo add support for more image formats like WebP?
	 */
	#[Override]
	protected function getImageResourceAsBlob(?string $type, int $quality): string
	{
		$type ??= $this->imageInfo['mime'];

		ob_start();

		switch ($type) {
			case 'gif':
			case 'image/gif':
				imagegif($this->imageResource);
				break;
			case 'jpg':
			case 'jpeg':
			case 'image/jpeg':
				imagejpeg($this->imageResource, quality: $quality);
				break;
			case 'png':
			case 'image/png':
				imagealphablending($this->imageResource, true);
				imagesavealpha($this->imageResource, true);
				imagepng($this->imageResource, quality: (int) (9 - (round(($quality / 100) * 9))));
				break;
			default:
				throw new ImageException(sprintf('Unsupported image type [ %s ].', $type));
		}

		return ob_get_clean();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @todo add support for more image formats like WebP?
	 */
	#[Override]
	protected function saveImageResource(string $imagePath, int $quality): void
	{
		// Get the file extension

		$extension = pathinfo($imagePath, PATHINFO_EXTENSION);

		// Save image

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

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function rotate(int $degrees): void
	{
		$width  = imagesx($this->imageResource);
		$height = imagesy($this->imageResource);

		$transparent = imagecolorallocatealpha($this->imageResource, 0, 0, 0, 127);

		if ($this->imageInfo[2] === IMAGETYPE_GIF) {
			$temp = imagecreatetruecolor($width, $height);

			imagefill($temp, 0, 0, $transparent);

			imagecopy($temp, $this->imageResource, 0, 0, 0, 0, $width, $height);

			$this->imageResource = $temp;
		}

		$this->imageResource = imagerotate($this->imageResource, (360 - $degrees), $transparent);

		imagecolortransparent($this->imageResource, $transparent);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function resize(int $width, ?int $height = null, AspectRatio $aspectRatio = AspectRatio::IGNORE): void
	{
		$oldWidth  = imagesx($this->imageResource);
		$oldHeight = imagesy($this->imageResource);

		[$newWidth, $newHeight] = $this->calculateNewDimensions($width, $height, $oldWidth, $oldHeight, $aspectRatio);

		$resized = imagecreatetruecolor($newWidth, $newHeight);

		$transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);

		imagefill($resized, 0, 0, $transparent);

		imagecopyresampled($resized, $this->imageResource, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);

		imagecolortransparent($resized, $transparent);

		$this->imageResource = $resized;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function crop(int $width, int $height, int $x, int $y): void
	{
		$oldWidth  = imagesx($this->imageResource);
		$oldHeight = imagesy($this->imageResource);

		$crop = imagecreatetruecolor($width, $height);

		$transparent = imagecolorallocatealpha($crop, 0, 0, 0, 127);

		imagefill($crop, 0, 0, $transparent);

		imagecopy($crop, $this->imageResource, 0, 0, $x, $y, $oldWidth, $oldHeight);

		imagecolortransparent($crop, $transparent);

		$this->imageResource = $crop;
	}
}
