<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image;

/**
 * Image.
 */
interface ImageInterface
{
	/**
	 * Returns the image width in pixels.
	 */
	public function getWidth(): int;

	/**
	 * Returns the image height in pixels.
	 */
	public function getHeight(): int;

	/**
	 * Returns an array containing the image dimensions in pixels.
	 *
	 * @return array{width: int, height: int}
	 */
	public function getDimensions(): array;

	/**
	 * Rotates the image using the given angle in degrees.
	 */
	public function rotate(int $degrees): void;

	/**
	 * Resizes the image to the chosen size.
	 */
	public function resize(int $width, ?int $height = null, AspectRatio $aspectRatio = AspectRatio::IGNORE);

	/**
	 * Crops the image.
	 */
	public function crop(int $width, int $height, int $x, int $y): void;

	/**
	 * Returns the image resource as a blob.
	 */
	public function getImageBlob(?string $type = null, int $quality = 95): string;

	/**
	 * Saves the image resource to a file.
	 */
	public function save(?string $imagePath = null, int $quality = 95): void;
}
