<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image;

use mako\pixel\image\exceptions\ImageException;
use mako\pixel\image\operations\OperationInterface;
use Override;
use ReflectionClass;

use function base64_encode;
use function file_exists;
use function is_readable;
use function is_writable;
use function max;
use function min;
use function pathinfo;
use function rewind;
use function sprintf;
use function str_starts_with;
use function strtolower;

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
	 * Image path.
	 */
	protected ?string $imagePath = null;

	/**
	 * Mime type.
	 */
	protected string $mimeType;

	/**
	 * Constructor.
	 */
	final public function __construct(
		string $imagePath
	) {
		if (file_exists($imagePath) === false) {
			throw new ImageException(sprintf('The image [ %s ] does not exist.', $imagePath));
		}

		if (!is_readable($imagePath)) {
			throw new ImageException(sprintf('The image [ %s ] is not readable.', $imagePath));
		}

		$this->imageResource = $this->createImageResourceFromPath($imagePath);
	}

	/**
	 * Clones the object.
	 */
	abstract public function __clone();

	/**
	 * Destructor.
	 */
	final public function __destruct()
	{
		$this->destroyImageResource();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	final public static function fromPath(string $imagePath): static
	{
		return new static($imagePath);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	final public static function fromBlob(string $blob): static
	{
		$image = (new ReflectionClass(static::class))->newInstanceWithoutConstructor();

		$image->imageResource = $image->createImageResourceFromBlob($blob);

		return $image;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	final public static function fromStream(mixed $stream): static
	{
		$image = (new ReflectionClass(static::class))->newInstanceWithoutConstructor();

		$image->imageResource = $image->createImageResourceFromStream($stream);

		return $image;
	}

	/**
	 * Returns a normalized mime type.
	 */
	protected function normalizeMimeType(string $type): string
	{
		$type = strtolower($type);
		$type = str_starts_with($type, 'image/') ? $type : "image/{$type}";

		return match ($type) {
			'image/jpg', 'image/jpeg' => 'image/jpeg',
			'image/tif', 'image/tiff' => 'image/tiff',
			default                   => $type,
		};
	}

	/**
	 * Creates an image resource from a file path.
	 */
	abstract protected function createImageResourceFromPath(string $imagePath): object;

	/**
	 * Creates an image resource from a binary blob.
	 */
	abstract protected function createImageResourceFromBlob(string $blob): object;

	/**
	 * Creates an image resource from a stream.
	 */
	abstract protected function createImageResourceFromStream(mixed $stream): object;

	/**
	 * Destroys an image resource.
	 */
	abstract protected function destroyImageResource(): void;

	/**
	 * Returns the image resouce as a blob.
	 */
	abstract protected function getImageResourceAsBlob(?string $type, int $quality): string;

	/**
	 * Writes the image resource to a stream.
	 *
	 * @param resource $stream
	 */
	abstract protected function writeImageResourceToStream(mixed $stream, ?string $type, int $quality): void;

	/**
	 * Save an image resource.
	 */
	abstract protected function saveImageResource(string $imagePath, int $quality): void;

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getImageResource(): object
	{
		return $this->imageResource;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getMimeType(): string
	{
		return $this->mimeType;
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
	 * {@inheritDoc}
	 */
	#[Override]
	public function apply(OperationInterface $operation): static
	{
		$operation->apply($this->imageResource);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function applyOnClone(OperationInterface $operation): static
	{
		return (clone $this)->apply($operation);
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
	#[Override]
	public function toBlob(?string $type = null, int $quality = 95): string
	{
		return $this->getImageResourceAsBlob($type, $this->normalizeImageQuality($quality));
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function toBase64(?string $type = null, int $quality = 95): string
	{
		return base64_encode($this->toBlob($type, $quality));
	}

	/**
	 * {Returns the output mime type of the image resource.
	 */
	protected function getOuputMimeType(?string $type): string
	{
		return $type === null
			? $this->mimeType
			: $this->normalizeMimeType($type);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function toDataUri(?string $type = null, int $quality = 95): string
	{
		return "data:{$this->getOuputMimeType($type)};base64,{$this->toBase64($type, $quality)}";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function toStream(?string $type = null, int $quality = 95, StreamStorage $stream = StreamStorage::Temp): mixed
	{
		$stream = $stream->create();

		$this->writeImageResourceToStream($stream, $type, $this->normalizeImageQuality($quality));

		rewind($stream);

		return $stream;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function save(?string $imagePath = null, int $quality = 95): void
	{
		$imagePath ??= $this->imagePath ?? throw new ImageException('An image path must be provided when saving images created from a blob.');

		if (file_exists($imagePath)) {
			if (!is_writable($imagePath)) {
				throw new ImageException(sprintf('The file [ %s ] is not writable.', $imagePath));
			}
		}
		else {
			$directory = pathinfo($imagePath, PATHINFO_DIRNAME);

			if (!file_exists($directory)) {
				throw new ImageException(sprintf('The directory [ %s ] does not exist.', $imagePath));
			}

			if (!is_writable($directory)) {
				throw new ImageException(sprintf('The directory [ %s ] is not writable.', $directory));
			}
		}

		$this->saveImageResource($imagePath, $this->normalizeImageQuality($quality));
	}
}
