<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixl\processors;

use mako\pixl\Image;

/**
 * Image manipulation processor interface.
 */
interface ProcessorInterface
{
	/**
	 * Opens the image we want to work with.
	 */
	public function open(string $image);

	/**
	 * Creates a snapshot of the image resource.
	 */
	public function snapshot();

	/**
	 * Restores an image snapshot.
	 */
	public function restore();

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
	 */
	public function getDimensions(): array;

	/**
	 * Rotates the image using the given angle in degrees.
	 */
	public function rotate(int $degrees);

	/**
	 * Resizes the image to the chosen size.
	 */
	public function resize(int $width, ?int $height = null, int $aspectRatio = Image::RESIZE_IGNORE);

	/**
	 * Crops the image.
	 */
	public function crop(int $width, int $height, int $x, int $y): void;

	/**
	 * Flips the image.
	 */
	public function flip(int $direction = Image::FLIP_HORIZONTAL): void;

	/**
	 * Adds a watermark to the image.
	 */
	public function watermark(string $file, int $position = Image::WATERMARK_TOP_LEFT, int $opacity = 100): void;

	/**
	 * Adjust image brightness.
	 */
	public function brightness(int $level = 50);

	/**
	 * Converts image to greyscale.
	 */
	public function greyscale(): void;

	/**
	 * Converts image to sepia.
	 */
	public function sepia(): void;

	/**
	 * Converts image to bitonal.
	 */
	public function bitonal(): void;

	/**
	 * Colorize the image.
	 *
	 * @param string $color Hex value
	 */
	public function colorize(string $color): void;

	/**
	 * Sharpens the image.
	 */
	public function sharpen(): void;

	/**
	 * Pixelates the image.
	 */
	public function pixelate(int $pixelSize = 10);

	/**
	 * Negates the image.
	 */
	public function negate(): void;

	/**
	 * Adds a border to the image.
	 */
	public function border(string $color = '#000', int $thickness = 5): void;

	/**
	 * Returns a string containing the image.
	 */
	public function getImageBlob(?string $type = null, int $quality = 95): string;

	/**
	 * Saves image to file.
	 */
	public function save(string $file, int $quality = 95): void;
}
