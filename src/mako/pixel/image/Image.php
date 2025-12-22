<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image;

use mako\pixel\image\exceptions\ImageException;
use Override;

use function file_exists;
use function is_readable;
use function is_writable;
use function max;
use function min;
use function pathinfo;
use function round;
use function sprintf;

/**
 * Base image.
 */
abstract class Image implements ImageInterface
{
	/**
	 * Image resource.
	 */
	protected ?object $imageResource = null;

	/**
	 * Snapshot image resource.
	 */
	protected ?object $snapshot = null;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $imagePath
	) {
		if (file_exists($imagePath) === false) {
			throw new ImageException(sprintf('The image [ %s ] does not exist.', $imagePath));
		}

		if (!is_readable($imagePath)) {
			throw new ImageException(sprintf('The image [ %s ] is not readable.', $imagePath));
		}

		$this->imageResource = $this->createImageResource($imagePath);
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		$this->destroyImageResource();
	}

	/**
	 * Creates an image resource.
	 */
	abstract protected function createImageResource(string $imagePath): object;

	/**
	 * Destroys an image resource.
	 */
	abstract protected function destroyImageResource(): void;

	/**
	 * Returns the image resouce as a blob.
	 */
	abstract protected function getImageResourceAsBlob(?string $type, int $quality): string;

	/**
	 * Save an image resource.
	 */
	abstract protected function saveImageResource(string $imagePath, int $quality): void;

	/**
	 * Calculates new image dimensions.
	 */
	protected function calculateNewDimensions(int $width, ?int $height, int $oldWidth, int $oldHeight, AspectRatio $aspectRatio): array
	{
		if ($height === null) {
			$newWidth  = round($oldWidth * ($width / 100));
			$newHeight = round($oldHeight * ($width / 100));
		}
		else {
			if ($aspectRatio === AspectRatio::AUTO) {
				// Calculate smallest size based on given height and width while maintaining aspect ratio

				$percentage = min(($width / $oldWidth), ($height / $oldHeight));

				$newWidth  = round($oldWidth * $percentage);
				$newHeight = round($oldHeight * $percentage);
			}
			elseif ($aspectRatio === AspectRatio::WIDTH) {
				// Base new size on given width while maintaining aspect ratio

				$newWidth  = $width;
				$newHeight = round($oldHeight * ($width / $oldWidth));
			}
			elseif ($aspectRatio === AspectRatio::HEIGHT) {
				// Base new size on given height while maintaining aspect ratio

				$newWidth  = round($oldWidth * ($height / $oldHeight));
				$newHeight = $height;
			}
			else {
				// Ignone aspect ratio

				$newWidth  = $width;
				$newHeight = $height;
			}
		}

		return [$newWidth, $newHeight];
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getDimensions(): array
	{
		return ['width' => $this->getWidth(), 'height' => $this->getHeight()];
	}

	/**
	 * Makes sure that the quality is between 1 and 100.
	 */
	protected function normalizeImageQuality(int $quality): int
	{
		return max(min($quality, 100), 1);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getImageBlob(?string $type = null, int $quality = 95): string
	{
		return $this->getImageResourceAsBlob($type, $this->normalizeImageQuality($quality));
	}

	/**
	 * {@inheritDoc}
	 */
	public function save(?string $imagePath = null, int $quality = 95): void
	{
		$imagePath ??= $this->imagePath;

		if (file_exists($imagePath)) {
			if (!is_writable($imagePath)) {
				throw new ImageException(sprintf('The file [ %s ] isn\'t writable.', $imagePath));
			}
		}
		else {
			$directory = pathinfo($imagePath, PATHINFO_DIRNAME);

			if (!file_exists($directory)) {
				throw new ImageException(sprintf('The directory [ %s ] does not exist.', $imagePath));
			}

			if (!is_writable($directory)) {
				throw new ImageException(sprintf('The directory [ %s ] isn\'t writable.', $directory));
			}
		}

		$this->saveImageResource($imagePath, $this->normalizeImageQuality($quality));
	}
}
