<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image;

use Imagick;
use ImagickPixel;
use mako\pixel\image\operations\OperationInterface;
use Override;

use function array_last;
use function count;
use function explode;
use function pathinfo;
use function strtolower;
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
	final public function __clone()
	{
		$this->imageResource = clone $this->imageResource;
	}

	/**
	 * Are we working with an animated gif?
	 */
	protected bool $isAnimatedGif = false;

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
		if ($type !== null) {
			$type = strtolower(array_last(explode('/', $type)));

			$this->imageResource->setImageFormat($type);
		}

		if ($type === 'gif' || $this->mimeType === 'image/gif') {
			foreach ($this->imageResource as $frame) {
				$frame->evaluateImage(Imagick::EVALUATE_THRESHOLD, 0, Imagick::CHANNEL_ALPHA);
			}

			if ($this->isAnimatedGif) {
				return $this->imageResource->getImagesBlob();
			}
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
		$type = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

		$this->imageResource->setImageFormat($type);

		if ($type === 'gif') {
			foreach ($this->imageResource as $frame) {
				$frame->evaluateImage(Imagick::EVALUATE_THRESHOLD, 0, Imagick::CHANNEL_ALPHA);
			}

			if ($this->isAnimatedGif) {
				$this->imageResource->writeImages($imagePath, true);

				return;
			}
		}

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

			$rgba = $pixel->getColor(2); // 2 = RGBA normalized to 0-255

			if ($rgba['a'] === 0) {
				continue;
			}

			$colors[] = new Color($rgba['r'], $rgba['g'], $rgba['b'], $rgba['a']);
		}

		$image->clear();
		$image->destroy();

		return $colors;
	}
}
