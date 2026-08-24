<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image;

use Imagick;
use ImagickPixel;
use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\operations\OperationInterface;
use Override;

use function array_last;
use function explode;
use function pathinfo;
use function strtolower;

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
	 * Are we working with an animated gif?
	 */
	protected bool $isAnimatedGif = false;

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	final public function __clone()
	{
		$this->imageResource = clone $this->imageResource;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function createImageResource(Dimensions $dimensions, Color $fill): object
	{
		$this->mimeType = 'image/png';

		$imageResource = new Imagick;

		$imageResource->newImage(
			$dimensions->width,
			$dimensions->height,
			new ImagickPixel($fill->toHexaString()),
			'png'
		);

		$imageResource->setImageColorspace(Imagick::COLORSPACE_SRGB);

		return $imageResource;
	}

	/**
	 * Stores the mime type and performs a gif check.
	 */
	protected function collectMimeTypeAndPerformGifCheck(Imagick $imageResource): Imagick
	{
		$this->mimeType = $this->normalizeMimeType($imageResource->getImageFormat());

		if ($this->mimeType === 'image/gif' && $imageResource->getNumberImages() > 1) {
			$this->isAnimatedGif = true;

			$imageResource = $imageResource->coalesceImages();
		}

		return $imageResource;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function createImageResourceFromPath(string $imagePath): object
	{
		$this->imagePath = $imagePath;

		$imageResource = new Imagick($imagePath);

		return $this->collectMimeTypeAndPerformGifCheck($imageResource);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function createImageResourceFromBlob(string $blob): object
	{
		$imageResource = new Imagick;

		$imageResource->readImageBlob($blob);

		return $this->collectMimeTypeAndPerformGifCheck($imageResource);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function createImageResourceFromStream(mixed $stream): object
	{
		$imageResource = new Imagick;

		$imageResource->readImageFile($stream);

		return $this->collectMimeTypeAndPerformGifCheck($imageResource);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function destroyImageResource(): void
	{
		if ($this->imageResource !== null) {
			$this->imageResource->clear();
			$this->imageResource->destroy();

			$this->imageResource = null;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function apply(OperationInterface $operation): static
	{
		if (!$this->isAnimatedGif) {
			return parent::apply($operation);
		}

		foreach ($this->imageResource as $frame) {
			$operation->apply($frame);

			$frame->setImagePage(0, 0, 0, 0);
		}

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function getImageResourceAsBlob(?string $type, int $quality): string
	{
		$image = clone $this->imageResource;

		try {
			if ($type !== null) {
				$type = strtolower(array_last(explode('/', $type)));

				$image->setImageFormat($type);
			}

			if ($this->isAnimatedGif && ($type === 'gif' || ($type === null && $this->mimeType === 'image/gif'))) {
				foreach ($image as $frame) {
					$frame->evaluateImage(Imagick::EVALUATE_THRESHOLD, 0, Imagick::CHANNEL_ALPHA);
				}

				return $image->getImagesBlob();
			}

			$image->setImageCompressionQuality($quality);

			return $image->getImageBlob();
		}
		finally {
			$image->clear();
			$image->destroy();
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function writeImageResourceToStream(mixed $stream, ?string $type, int $quality): void
	{
		$image = clone $this->imageResource;

		try {
			if ($type !== null) {
				$type = strtolower(array_last(explode('/', $type)));

				$image->setImageFormat($type);
			}

			if ($this->isAnimatedGif && ($type === 'gif' || ($type === null && $this->mimeType === 'image/gif'))) {
				foreach ($image as $frame) {
					$frame->evaluateImage(Imagick::EVALUATE_THRESHOLD, 0, Imagick::CHANNEL_ALPHA);
				}

				$image->writeImagesFile($stream);

				return;
			}

			$image->setImageCompressionQuality($quality);

			$image->writeImageFile($stream);
		}
		finally {
			$image->clear();
			$image->destroy();
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function saveImageResource(string $imagePath, int $quality): void
	{
		$image = clone $this->imageResource;

		try {
			$type = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

			$image->setImageFormat($type);

			if ($this->isAnimatedGif && $type === 'gif') {
				foreach ($image as $frame) {
					$frame->evaluateImage(Imagick::EVALUATE_THRESHOLD, 0, Imagick::CHANNEL_ALPHA);
				}

				$image->writeImages($imagePath, true);

				return;
			}

			$image->setImageCompressionQuality($quality);

			$image->writeImage($imagePath);
		}
		finally {
			$image->clear();
			$image->destroy();
		}
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
}
