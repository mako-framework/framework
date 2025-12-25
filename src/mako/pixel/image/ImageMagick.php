<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image;

use Imagick;
use ImagickPixel;
use mako\pixel\image\exceptions\ImageException;
use mako\pixel\image\operations\OperationInterface;
use Override;

use function array_last;
use function count;
use function explode;
use function pathinfo;
use function round;
use function strtolower;
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
	 * Are we working with an animated gif?
	 */
	protected bool $isAnimatedGif = false;

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function createImageResource(string $imagePath): object
	{
		$imageResource = new Imagick($imagePath);

		if (strtolower($imageResource->getImageFormat()) === 'gif' && $imageResource->getNumberImages() > 1) {
			$this->isAnimatedGif = true;

			$imageResource = $imageResource->coalesceImages();
		}

		return $imageResource;
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

		if ($type === 'gif' || strtolower($this->imageResource->getImageFormat()) === 'gif') {
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
