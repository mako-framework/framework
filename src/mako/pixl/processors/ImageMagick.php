<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixl\processors;

use Imagick;
use ImagickPixel;
use mako\pixl\Image;
use mako\pixl\processors\exceptions\ProcessorException;
use mako\pixl\processors\traits\CalculateNewDimensionsTrait;

use function preg_match;
use function strpos;
use function vsprintf;

/**
 * ImageMagick processor.
 */
class ImageMagick implements ProcessorInterface
{
	use CalculateNewDimensionsTrait;

	/**
	 * Imagick instance.
	 */
	protected null|Imagick $image = null;

	/**
	 * Imagick instance.
	 */
	protected null|Imagick $snapshot = null;

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		if($this->image instanceof Imagick)
		{
			$this->image->destroy();
		}

		if($this->snapshot instanceof Imagick)
		{
			$this->snapshot->destroy();
		}
	}

	/**
	 * Add the hash character (#) if its missing.
	 */
	public function normalizeHex(string $hex): string
	{
		if(preg_match('/^(#?[a-f0-9]{3}){1,2}$/i', $hex) !== 1)
		{
			throw new ProcessorException(vsprintf('Invalid HEX value [ %s ].', [$hex]));
		}

		return (strpos($hex, '#') !== 0) ? "#{$hex}" : $hex;
	}

	/**
	 * {@inheritDoc}
	 */
	public function open(string $image): void
	{
		$this->image = new Imagick($image);
	}

	/**
	 * {@inheritDoc}
	 */
	public function snapshot(): void
	{
		$this->snapshot = clone $this->image;
	}

	/**
	 * {@inheritDoc}
	 */
	public function restore(): void
	{
		if(!($this->snapshot instanceof Imagick))
		{
			throw new ProcessorException('No snapshot to restore.');
		}

		$this->image = $this->snapshot;

		$this->snapshot = null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getWidth(): int
	{
		return $this->image->getImageWidth();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHeight(): int
	{
		return $this->image->getImageHeight();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDimensions(): array
	{
		return ['width' => $this->getWidth(), 'height' => $this->getHeight()];
	}

	/**
	 * {@inheritDoc}
	 */
	public function rotate(int $degrees): void
	{
		$this->image->rotateImage(new ImagickPixel('none'), $degrees);
	}

	/**
	 * {@inheritDoc}
	 */
	public function resize(int $width, ?int $height = null, int $aspectRatio = Image::RESIZE_IGNORE): void
	{
		$oldWidth  = $this->image->getImageWidth();
		$oldHeight = $this->image->getImageHeight();

		[$newWidth, $newHeight] = $this->calculateNewDimensions($width, $height, $oldWidth, $oldHeight, $aspectRatio);

		$this->image->scaleImage($newWidth, $newHeight);
	}

	/**
	 * {@inheritDoc}
	 */
	public function crop(int $width, int $height, int $x, int $y): void
	{
		$this->image->cropImage($width, $height, $x, $y);
	}

	/**
	 * {@inheritDoc}
	 */
	public function flip(int $direction = Image::FLIP_HORIZONTAL): void
	{
		if($direction ===  Image::FLIP_VERTICAL)
		{
			// Flips the image in the vertical direction

			$this->image->flipImage();
		}
		else
		{
			// Flips the image in the horizontal direction

			$this->image->flopImage();
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function watermark(string $file, int $position = Image::WATERMARK_TOP_LEFT, int $opacity = 100): void
	{
		$watermark = new Imagick($file);

		$watermarkW = $watermark->getImageWidth();
		$watermarkH = $watermark->getImageHeight();

		if($opacity < 100)
		{
			$watermark->evaluateImage(Imagick::EVALUATE_MULTIPLY, ($opacity / 100), Imagick::CHANNEL_ALPHA);
		}

		// Position the watermark.

		switch($position)
		{
			case Image::WATERMARK_TOP_RIGHT:
				$x = $this->image->getImageWidth() - $watermarkW;
				$y = 0;
				break;
			case Image::WATERMARK_BOTTOM_LEFT:
				$x = 0;
				$y = $this->image->getImageHeight() - $watermarkH;
				break;
			case Image::WATERMARK_BOTTOM_RIGHT:
				$x = $this->image->getImageWidth() - $watermarkW;
				$y = $this->image->getImageHeight() - $watermarkH;
				break;
			case Image::WATERMARK_CENTER:
				$x = ($this->image->getImageWidth() / 2) - ($watermarkW / 2);
				$y = ($this->image->getImageHeight() / 2) - ($watermarkH / 2);
				break;
			default:
				$x = 0;
				$y = 0;
		}

		$this->image->compositeImage($watermark, Imagick::COMPOSITE_OVER, $x, $y);

		$watermark->destroy();
	}

	/**
	 * {@inheritDoc}
	 */
	public function brightness(int $level = 50): void
	{
		$this->image->modulateImage(100 + $level, 100, 100);
	}

	/**
	 * {@inheritDoc}
	 */
	public function greyscale(): void
	{
		$this->image->setImageType(Imagick::IMGTYPE_GRAYSCALE);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sepia(): void
	{
		$this->image->sepiaToneImage(80);
	}

	/**
	 * {@inheritDoc}
	 */
	public function bitonal(): void
	{
		$this->image->quantizeImage(2, Imagick::COLORSPACE_GRAY, 5, false, true);
	}

	/**
	 * {@inheritDoc}
	 */
	public function colorize(string $color): void
	{
		$this->image->colorizeImage($this->normalizeHex($color), 1.0);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sharpen(): void
	{
		$this->image->sharpenImage(0, 1);
	}

	/**
	 * {@inheritDoc}
	 */
	public function pixelate(int $pixelSize = 10): void
	{
		$width = $this->image->getImageWidth();

		$height = $this->image->getImageHeight();

		$this->image->scaleImage((int) ($width / $pixelSize), (int) ($height / $pixelSize));

		$this->image->scaleImage($width, $height);
	}

	/**
	 * {@inheritDoc}
	 */
	public function negate(): void
	{
		$this->image->negateImage(false);
	}

	/**
	 * {@inheritDoc}
	 */
	public function border(string $color = '#000', int $thickness = 5): void
	{
		$this->image->shaveImage($thickness, $thickness);

		$this->image->borderImage($this->normalizeHex($color), $thickness, $thickness);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getImageBlob(?string $type = null, int $quality = 95): string
	{
		if($type !== null)
		{
			if(!$this->image->setImageFormat($type))
			{
				throw new ProcessorException(vsprintf('Unsupported image type [ %s ].', [$type]));
			}
		}

		// Set image quality

		$this->image->setImageCompressionQuality($quality);

		// Return image blob

		return $this->image->getImageBlob();
	}

	/**
	 * {@inheritDoc}
	 */
	public function save(string $file, int $quality = 95): void
	{
		// Set image quality

		$this->image->setImageCompressionQuality($quality);

		// Save image

		$this->image->writeImage($file);
	}
}
