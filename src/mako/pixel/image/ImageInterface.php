<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image;

use mako\pixel\image\geometry\Dimensions;
use mako\pixel\image\inspectors\InspectorInterface;
use mako\pixel\image\operations\OperationInterface;

/**
 * Image.
 */
interface ImageInterface
{
	/**
	 * Creates a blank image instance.
	 */
	public static function create(Dimensions $dimensions, Color $fill = new Color(0, 0, 0, 0)): static;

	/**
	 * Creates an image instance from a file path.
	 */
	public static function fromPath(string $imagePath): static;

	/**
	 * Creates an image instance from a binary blob.
	 */
	public static function fromBlob(string $blob): static;

	/**
	 * Creates an image instance from a stream.
	 *
	 * @param resource $stream
	 */
	public static function fromStream(mixed $stream): static;

	/**
	 * Returns the underlying image resource object.
	 */
	public function getImageResource(): object;

	/**
	 * Returns the mime type of the image.
	 */
	public function getMimeType(): string;

	/**
	 * Returns the image width in pixels.
	 */
	public function getWidth(): int;

	/**
	 * Returns the image height in pixels.
	 */
	public function getHeight(): int;

	/**
	 * Returns a dimensions object.
	 */
	public function getDimensions(): Dimensions;

	/**
	 * Returns information about the image using the given inspector.
	 *
	 * @template T
	 * @param  InspectorInterface<T> $inspector
	 * @return T
	 */
	public function inspect(InspectorInterface $inspector): mixed;

	/**
	 * Applies an image operation.
	 */
	public function apply(OperationInterface $operation): static;

	/**
	 * Applies an image operation on a cloned instance.
	 */
	public function applyOnClone(OperationInterface $operation): static;

	/**
	 * Returns the image resource as a blob.
	 */
	public function toBlob(?string $type = null, int $quality = 95): string;

	/**
	 * Returns the image resource as a base64 encoded blob.
	 */
	public function toBase64(?string $type = null, int $quality = 95): string;

	/**
	 * Returns the image resource as a data uri.
	 */
	public function toDataUri(?string $type = null, int $quality = 95): string;

	/**
	 * Returns the image resource as a data stream.
	 *
	 * @return resource
	 */
	public function toStream(?string $type = null, int $quality = 95, StreamStorage $stream = StreamStorage::Temp): mixed;

	/**
	 * Saves the image resource to a file.
	 */
	public function save(?string $imagePath = null, int $quality = 95): void;
}
