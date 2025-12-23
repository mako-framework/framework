<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image;

use mako\pixel\image\operations\OperationInterface;

/**
 * Image.
 */
interface ImageInterface
{
	/**
	 * Returns the underlying image resource object.
	 */
	public function getImageResource(): object;

	/**
	 * Creates a snapshot of the image resource.
	 */
	public function snapshot(): void;

	/**
	 * Restores an image snapshot.
	 */
	public function restore(): void;

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
	 * Returns the n top colors found in the image.
	 *
	 * @return Color[]
	 */
	public function getTopColors(int $limit = 5, bool $ignoreTransparent = true): array;

	/**
	 * Applies an image operation.
	 */
	public function apply(OperationInterface $operation);

	/**
	 * Returns the image resource as a blob.
	 */
	public function getImageBlob(?string $type = null, int $quality = 95): string;

	/**
	 * Saves the image resource to a file.
	 */
	public function save(?string $imagePath = null, int $quality = 95): void;
}
