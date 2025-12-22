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

use function count;
use function round;
use function sprintf;
use function usort;

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
		$this->imageResource->clear();
		$this->imageResource->destroy();

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

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function flip(Flip $direction = Flip::HORIZONTAL): void
	{
		if ($direction ===  Flip::VERTICAL) {
			$this->imageResource->flipImage();
		}
		else {
			$this->imageResource->flopImage();
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function watermark(string $file, WatermarkPosition $position = WatermarkPosition::BOTTOM_RIGHT, int $opacity = 100): void
	{
		$watermark = new Imagick($file);

		$watermarkW = $watermark->getImageWidth();
		$watermarkH = $watermark->getImageHeight();

		if ($opacity < 100) {
			$watermark->evaluateImage(Imagick::EVALUATE_MULTIPLY, ($opacity / 100), Imagick::CHANNEL_ALPHA);
		}

		// Position the watermark.

		switch ($position) {
			case WatermarkPosition::TOP_RIGHT:
				$x = $this->imageResource->getImageWidth() - $watermarkW;
				$y = 0;
				break;
			case WatermarkPosition::BOTTOM_LEFT:
				$x = 0;
				$y = $this->imageResource->getImageHeight() - $watermarkH;
				break;
			case WatermarkPosition::BOTTOM_RIGHT:
				$x = $this->imageResource->getImageWidth() - $watermarkW;
				$y = $this->imageResource->getImageHeight() - $watermarkH;
				break;
			case WatermarkPosition::CENTER:
				$x = ($this->imageResource->getImageWidth() / 2) - ($watermarkW / 2);
				$y = ($this->imageResource->getImageHeight() / 2) - ($watermarkH / 2);
				break;
			default:
				$x = 0;
				$y = 0;
		}

		$this->imageResource->compositeImage($watermark, Imagick::COMPOSITE_OVER, $x, $y);

		$watermark->destroy();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function brightness(int $level = 50): void
	{
		$this->imageResource->modulateImage(100 + $level, 100, 100);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function greyscale(): void
	{
		$this->imageResource->setImageType(Imagick::IMGTYPE_GRAYSCALE);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function sepia(): void
	{
		$this->imageResource->sepiaToneImage(80);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function bitonal(): void
	{
		$this->imageResource->quantizeImage(2, Imagick::COLORSPACE_GRAY, 5, false, true);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function colorize(Color $color): void
	{
		$this->imageResource->colorizeImage($color->toHexString(), $color->getAlpha() / 255);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function sharpen(): void
	{
		$this->imageResource->sharpenImage(0, 1);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function pixelate(int $pixelSize = 10): void
	{
		$width = $this->imageResource->getImageWidth();

		$height = $this->imageResource->getImageHeight();

		$this->imageResource->scaleImage((int) ($width / $pixelSize), (int) ($height / $pixelSize));

		$this->imageResource->scaleImage($width, $height);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function negate(): void
	{
		$this->imageResource->negateImage(false);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function border(Color $color = new Color(0, 0, 0), int $thickness = 5): void
	{
		$this->imageResource->shaveImage($thickness, $thickness);

		$this->imageResource->borderImage($color->toHexString(), $thickness, $thickness);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getTopColors(int $limit = 5, bool $ignoreTransparent = true): array
	{
		$image = clone $this->imageResource;

		// Ensure consistent colorspace

		$image->setImageColorspace(Imagick::COLORSPACE_RGB);

		// Reduce noise by quantizing

		$image->quantizeImage(64, Imagick::COLORSPACE_RGB, 0, false, false);

		// Get full histogram

		$histogram = $image->getImageHistogram();

		// Sort by pixel count (descending)

		usort($histogram, fn (ImagickPixel $a, ImagickPixel $b) => $b->getColorCount() <=> $a->getColorCount());

		// Collect the top n colors

		$colors = [];

		foreach ($histogram as $pixel) {
			if (count($colors) >= $limit) {
				break;
			}

			$rgba = $pixel->getColor(true);

			$alpha = $rgba['a'] ?? 1.0;

			if ($ignoreTransparent && $alpha < 0.1) {
				continue;
			}

			$colors[] = new Color(
				(int) round($rgba['r'] * 255),
				(int) round($rgba['g'] * 255),
				(int) round($rgba['b'] * 255),
				(int) round($alpha * 255)
			);
		}

		// Destroy the image resource and return the top n colors

		$image->clear();
		$image->destroy();

		$image = null;

		return $colors;
	}
}
