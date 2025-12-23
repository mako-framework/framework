<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image;

use Imagick;
use ImagickDraw;
use ImagickPixel;
use mako\pixel\image\exceptions\ImageException;
use Override;

use function abs;
use function count;
use function max;
use function min;
use function round;
use function sprintf;
use function usort;

/**
 * ImageMagick.
 *
 * @see https://www.php.net/manual/en/book.imagick.php
 *
 * @property ?Imagick $imageResource
 * @property ?Imagick $snapshot
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

		if ($this->snapshot !== null) {
			$this->snapshot->clear();
			$this->snapshot->destroy();

			$this->snapshot = null;
		}
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
	public function snapshot(): void
	{
		$this->snapshot = clone $this->imageResource;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function restore(): void
	{
		if ($this->imageResource === null) {
			throw new ImageException('No snapshot to restore.');
		}

		$this->imageResource = clone $this->snapshot;

		$this->snapshot->clear();
		$this->snapshot->destroy();

		$this->snapshot = null;
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
		if ($degrees === 0) {
			return;
		}

		$this->imageResource->rotateImage(new ImagickPixel('none'), $degrees);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function resize(int $width, ?int $height = null, AspectRatio $aspectRatio = AspectRatio::AUTO): void
	{
		$oldWidth = $this->imageResource->getImageWidth();
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
	public function watermark(string $file, WatermarkPosition $position = WatermarkPosition::BOTTOM_RIGHT, int $opacity = 100, int $margin = 0): void
	{
		$watermark = new Imagick($file);

		$watermarkWidth = $watermark->getImageWidth();
		$watermarkHeight = $watermark->getImageHeight();

		if ($opacity < 100) {
			$watermark->evaluateImage(Imagick::EVALUATE_MULTIPLY, ($opacity / 100), Imagick::CHANNEL_ALPHA);
		}

		switch ($position) {
			case WatermarkPosition::TOP_RIGHT:
				$x = $this->imageResource->getImageWidth() - $watermarkWidth - $margin;
				$y = 0 + $margin;
				break;
			case WatermarkPosition::BOTTOM_LEFT:
				$x = 0 + $margin;
				$y = $this->imageResource->getImageHeight() - $watermarkHeight - $margin;
				break;
			case WatermarkPosition::BOTTOM_RIGHT:
				$x = $this->imageResource->getImageWidth() - $watermarkWidth - $margin;
				$y = $this->imageResource->getImageHeight() - $watermarkHeight - $margin;
				break;
			case WatermarkPosition::CENTER:
				$x = ($this->imageResource->getImageWidth() - $watermarkWidth) / 2;
				$y = ($this->imageResource->getImageHeight() - $watermarkHeight) / 2;
				break;
			default:
				$x = 0 + $margin;
				$y = 0 + $margin;
		}

		$this->imageResource->compositeImage($watermark, Imagick::COMPOSITE_OVER, $x, $y);

		$watermark->clear();
		$watermark->destroy();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function brightness(int $level = 0): void
	{
		if ($level === 0) {
			return;
		}

		$level = $this->normalizeLevel($level);

		$this->imageResource->sigmoidalContrastImage($level > 0, abs($level) / 100 * 8, 0.5);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function contrast(int $level = 0): void
	{
		if ($level === 0) {
			return;
		}

		$level = $this->normalizeLevel($level);

		$factor = 1 + (((100 + $level) / 100) - 1) * 0.8;

		$iterator = $this->imageResource->getPixelIterator();

		foreach ($iterator as $row => $pixels) {
			foreach ($pixels as $col => $pixel) {
				$colors = $pixel->getColor();

				$r = max(0, min(255, (($colors['r'] / 255 - 0.5) * $factor + 0.5) * 255));
				$g = max(0, min(255, (($colors['g'] / 255 - 0.5) * $factor + 0.5) * 255));
				$b = max(0, min(255, (($colors['b'] / 255 - 0.5) * $factor + 0.5) * 255));

				$pixel->setColor("rgb($r, $g, $b)");
			}

			$iterator->syncIterator();
		}

		$iterator->clear();
		$iterator->destroy();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function saturation(int $level = 0): void
	{
		if ($level === 0) {
			return;
		}

		$level = $this->normalizeLevel($level);

		$saturation = 100 + $level;

		$this->imageResource->modulateImage(100, $saturation, 100);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function temperature(int $level = 0): void
	{
		if ($level == 0) {
			return;
		}

		$level = $this->normalizeLevel($level);

		$shift = $level * 0.0022;

		if ($shift > 0) {
			$this->imageResource->evaluateImage(Imagick::EVALUATE_MULTIPLY, 1 + $shift, Imagick::CHANNEL_RED);
			$this->imageResource->evaluateImage(Imagick::EVALUATE_MULTIPLY, 1 - $shift, Imagick::CHANNEL_BLUE);
		}
		elseif ($shift < 0) {
			$this->imageResource->evaluateImage(Imagick::EVALUATE_MULTIPLY, 1 + abs($shift), Imagick::CHANNEL_BLUE);
			$this->imageResource->evaluateImage(Imagick::EVALUATE_MULTIPLY, 1 - abs($shift), Imagick::CHANNEL_RED);
		}
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
		$this->imageResource->colorMatrixImage([
			0.393 * 0.85, 0.769 * 0.85, 0.189 * 0.85, 0, 0,
			0.349 * 0.85, 0.686 * 0.85, 0.168 * 0.85, 0, 0,
			0.272 * 0.85, 0.534 * 0.85, 0.131 * 0.85, 0, 0,
			0,            0,            0,            1, 0,
			0,            0,            0,            0, 1,
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function bitonal(): void
	{
		$this->imageResource->setImageType(Imagick::IMGTYPE_BILEVEL);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function colorize(Color $color): void
	{
		$pixel = new ImagickPixel($color->toRgbaString());

		$this->imageResource->colorizeImage($pixel, $pixel);
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
		$alpha = clone $this->imageResource;

		$this->imageResource->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);

		$this->imageResource->negateImage(false);

		$this->imageResource->compositeImage($alpha, Imagick::COMPOSITE_COPYOPACITY, 0, 0);

		$alpha->clear();
		$alpha->destroy();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function border(Color $color = new Color(0, 0, 0), int $thickness = 5): void
	{
		$draw = new ImagickDraw;

		$draw->setStrokeColor(new ImagickPixel($color->toRgbaString()));
		$draw->setStrokeWidth($thickness);
		$draw->setFillOpacity(0);
		$draw->setStrokeAntialias(true);

		$width = $this->imageResource->getImageWidth();
		$height = $this->imageResource->getImageHeight();

		$draw->rectangle(
			$thickness / 2,
			$thickness / 2,
			$width - $thickness / 2,
			$height - $thickness / 2
		);

		$this->imageResource->drawImage($draw);

		$draw->clear();
		$draw->destroy();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getTopColors(int $limit = 5, bool $ignoreTransparent = true): array
	{
		$image = clone $this->imageResource;

		$image->setImageColorspace(Imagick::COLORSPACE_RGB);

		$image->quantizeImage(64, Imagick::COLORSPACE_RGB, 0, false, false);

		$histogram = $image->getImageHistogram();

		usort($histogram, fn (ImagickPixel $a, ImagickPixel $b): int => $b->getColorCount() <=> $a->getColorCount());

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

		$image->clear();
		$image->destroy();

		return $colors;
	}
}
