<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image;

use Imagick;
use ImagickPixel;
use mako\pixel\image\exceptions\ImageException;
use Override;

/**
 * ImageMagick.
 *
 * @see https://www.php.net/manual/en/book.imagick.php
 *
 * @property ?Imagick $imageResource
 */
class ImageMagick extends Image
{

	/**
	 * {@inheritDoc}
	 */
	#[Override]
    protected function createImageResource(string $imagePath): object
    {
		return new Imagick($imagePath);
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
		if ($type !== null && !$this->imageResource->setImageFormat($type)) {
			throw new ImageException(sprintf('Unsupported image type [ %s ].', $type));
		}

		$this->imageResource->setImageCompressionQuality($quality);

		return $this->imageResource->getImageBlob();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function saveImageResource(string $imagePath, int $quality): void
	{
		$this->imageResource->setImageCompressionQuality($quality);

		$this->imageResource->writeImage($imagePath);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getWidth(): int
	{
		return $this->imageResource->getImageWidth();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getHeight(): int
	{
		return $this->imageResource->getImageHeight();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function rotate(int $degrees): void
	{
		$this->imageResource->rotateImage(new ImagickPixel('none'), $degrees);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function resize(int $width, ?int $height = null, AspectRatio $aspectRatio = AspectRatio::IGNORE): void
	{
		$oldWidth  = $this->imageResource->getImageWidth();
		$oldHeight = $this->imageResource->getImageHeight();

		[$newWidth, $newHeight] = $this->calculateNewDimensions($width, $height, $oldWidth, $oldHeight, $aspectRatio);

		$this->imageResource->scaleImage($newWidth, $newHeight);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function crop(int $width, int $height, int $x, int $y): void
	{
		$this->imageResource->cropImage($width, $height, $x, $y);
	}
}
