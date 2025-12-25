<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image;

use mako\pixel\image\exceptions\ImageException;
use Override;

use function array_keys;
use function array_map;
use function array_slice;
use function arsort;
use function explode;
use function getimagesize;
use function imagealphablending;
use function imageavif;
use function imagebmp;
use function imagecolorat;
use function imagecopy;
use function imagecreatefromavif;
use function imagecreatefromgif;
use function imagecreatefromjpeg;
use function imagecreatefrompng;
use function imagecreatefromwebp;
use function imagecreatetruecolor;
use function imagegif;
use function imagejpeg;
use function imagepng;
use function imagesavealpha;
use function imagesx;
use function imagesy;
use function imagewebp;
use function intval;
use function max;
use function min;
use function ob_get_clean;
use function ob_start;
use function pathinfo;
use function round;
use function sprintf;
use function strtolower;

/**
 * GD.
 *
 * @see https://www.php.net/manual/en/book.image.php
 *
 * @property ?\GdImage $imageResource
 * @property ?\GdImage $snapshot
 */
class Gd extends Image
{
	/**
	 * Mime type.
	 */
	protected ?string $mimeType = null;

	/**
	 * Returns information about the image.
	 */
	protected function getImageInfo(string $imagePath): array
	{
		$imageInfo = getimagesize($imagePath);

		if ($imageInfo === false) {
			throw new ImageException(sprintf('Unable to process the image [ %s ].', $imagePath));
		}

		return $imageInfo;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function createImageResource(string $imagePath): object
	{
		$imageInfo = $this->getImageInfo($imagePath);

		if ($this->mimeType === null) {
			$this->mimeType = $imageInfo['mime'];
		}

		return match ($imageInfo[2]) {
			IMAGETYPE_JPEG => imagecreatefromjpeg($imagePath),
			IMAGETYPE_GIF  => imagecreatefromgif($imagePath),
			IMAGETYPE_PNG  => imagecreatefrompng($imagePath),
			IMAGETYPE_WEBP => imagecreatefromwebp($imagePath),
			IMAGETYPE_AVIF => imagecreatefromavif($imagePath),
			default        => throw new ImageException(sprintf('Unable to create image resource from [ %s ]. Unsupported image type.', $imagePath)),
		};
    }

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function destroyImageResource(): void
	{
		$this->imageResource = null;

		$this->snapshot = null;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function getImageResourceAsBlob(?string $type, int $quality): string
	{
		$type ??= $this->mimeType;

		ob_start();

		switch (strtolower($type)) {
			case 'gif':
			case 'image/gif':
				imagegif($this->imageResource);
				break;
			case 'jpg':
			case 'jpeg':
			case 'image/jpeg':
				imagejpeg($this->imageResource, quality: $quality);
				break;
			case 'png':
			case 'image/png':
				imagealphablending($this->imageResource, true);
				imagesavealpha($this->imageResource, true);
				imagepng($this->imageResource, quality: (int) (9 - (round(($quality / 100) * 9))));
				break;
			case 'webp':
			case 'image/webp':
				imagewebp($this->imageResource, quality: $quality);
				break;
			case 'avif':
			case 'image/avif':
				imageavif($this->imageResource, quality: $quality);
				break;
			case 'bmp':
			case 'image/bmp':
				imagebmp($this->imageResource);
				break;
			default:
				throw new ImageException(sprintf('Unsupported image type [ %s ].', $type));
		}

		return ob_get_clean();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function saveImageResource(string $imagePath, int $quality): void
	{
		$extension = pathinfo($imagePath, PATHINFO_EXTENSION);

		switch (strtolower($extension)) {
			case 'gif':
				imagegif($this->imageResource, $imagePath);
				break;
			case 'jpg':
			case 'jpeg':
				imagejpeg($this->imageResource, $imagePath, $quality);
				break;
			case 'png':
				imagealphablending($this->imageResource, true);
				imagesavealpha($this->imageResource, true);
				imagepng($this->imageResource, $imagePath, (int) (9 - (round(($quality / 100) * 9))));
				break;
			case 'webp':
				imagewebp($this->imageResource, $imagePath, $quality);
				break;
			case 'avif':
				imageavif($this->imageResource, $imagePath, $quality);
				break;
			case 'bmp':
				imagebmp($this->imageResource, $imagePath);
				break;
			default:
				throw new ImageException(sprintf('Unable to save as [ %s ]. Unsupported image format.', $extension));
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function snapshot(): void
	{
		$width = imagesx($this->imageResource);
		$height = imagesy($this->imageResource);

		$this->snapshot = imagecreatetruecolor($width, $height);

		imagecopy($this->snapshot, $this->imageResource, 0, 0, 0, 0, $width, $height);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function restore(): void
	{
		if ($this->snapshot === null) {
			throw new ImageException('No snapshot to restore.');
		}

		$this->imageResource = $this->snapshot;

		$this->snapshot = null;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getWidth(): int
	{
		return imagesx($this->imageResource);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getHeight(): int
	{
		return imagesy($this->imageResource);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getTopColors(int $limit = 5, bool $ignoreTransparent = true): array
	{
		$step = 5;

		$width = imagesx($this->imageResource);
		$height = imagesy($this->imageResource);

		$buckets = [];

		for ($y = 0; $y < $height; $y += $step) {
			for ($x = 0; $x < $width; $x += $step) {
				$rgb = imagecolorat($this->imageResource, $x, $y);

				$alpha = 1 - ((($rgb & 0x7F000000) >> 24) / 127);

				if ($ignoreTransparent && $alpha < 0.1) {
					continue;
				}

				$r = max(0, min(255, (int) round((($rgb >> 16) & 0xFF) / 16) * 16));
				$g = max(0, min(255, (int) round((($rgb >> 8) & 0xFF) / 16) * 16));
				$b = max(0, min(255, (int) round(($rgb & 0xFF) / 16) * 16));

				$key = "$r,$g,$b,$alpha";

				$buckets[$key] = ($buckets[$key] ?? 0) + 1;
			}
		}

		arsort($buckets);

		$colors = [];

		foreach (array_slice(array_keys($buckets), 0, $limit) as $rgba) {
			[$r, $g, $b, $a] = array_map(intval(...), explode(',', $rgba));

			$colors[] = new Color($r, $g, $b, $a * 255);
		}

		return $colors;
	}
}
