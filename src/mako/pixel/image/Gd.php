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
use function array_sum;
use function arsort;
use function explode;
use function function_exists;
use function getimagesize;
use function imagealphablending;
use function imageavif;
use function imagecolorallocatealpha;
use function imagecolorat;
use function imagecolortransparent;
use function imageconvolution;
use function imagecopy;
use function imagecopyresampled;
use function imagecreatefromavif;
use function imagecreatefromgif;
use function imagecreatefromjpeg;
use function imagecreatefrompng;
use function imagecreatefromwebp;
use function imagecreatetruecolor;
use function imagefill;
use function imagefilter;
use function imagegif;
use function imagejpeg;
use function imagepng;
use function imagerectangle;
use function imagerotate;
use function imagesavealpha;
use function imagesetpixel;
use function imagesx;
use function imagesy;
use function imagewebp;
use function intval;
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
	 * Image info.
	 */
	protected ?array $imageInfo = null;

	/**
	 * Do we have access to filters?
	 */
	protected ?bool $hasFilters = null;

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

		if ($this->imageInfo === null) {
			$this->imageInfo = $imageInfo;
		}

		if ($this->hasFilters === null) {
			$this->hasFilters = function_exists('imagefilter');
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
		$type ??= $this->imageInfo['mime'];

		ob_start();

		switch ($type) {
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
		// Get the file extension

		$extension = pathinfo($imagePath, PATHINFO_EXTENSION);

		// Save image

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
			case 'image/webp':
				imagewebp($this->imageResource, $imagePath, $quality);
			case 'avif':
			case 'image/avif':
				imageavif($this->imageResource, quality: $quality);
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
		$width  = imagesx($this->imageResource);
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
	public function rotate(int $degrees): void
	{
		$width  = imagesx($this->imageResource);
		$height = imagesy($this->imageResource);

		$transparent = imagecolorallocatealpha($this->imageResource, 0, 0, 0, 127);

		if ($this->imageInfo[2] === IMAGETYPE_GIF) {
			$temp = imagecreatetruecolor($width, $height);

			imagefill($temp, 0, 0, $transparent);

			imagecopy($temp, $this->imageResource, 0, 0, 0, 0, $width, $height);

			$this->imageResource = $temp;
		}

		$this->imageResource = imagerotate($this->imageResource, (360 - $degrees), $transparent);

		imagecolortransparent($this->imageResource, $transparent);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function resize(int $width, ?int $height = null, AspectRatio $aspectRatio = AspectRatio::IGNORE): void
	{
		$oldWidth  = imagesx($this->imageResource);
		$oldHeight = imagesy($this->imageResource);

		[$newWidth, $newHeight] = $this->calculateNewDimensions($width, $height, $oldWidth, $oldHeight, $aspectRatio);

		$resized = imagecreatetruecolor($newWidth, $newHeight);

		$transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);

		imagefill($resized, 0, 0, $transparent);

		imagecopyresampled($resized, $this->imageResource, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);

		imagecolortransparent($resized, $transparent);

		$this->imageResource = $resized;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function crop(int $width, int $height, int $x, int $y): void
	{
		$oldWidth  = imagesx($this->imageResource);
		$oldHeight = imagesy($this->imageResource);

		$crop = imagecreatetruecolor($width, $height);

		$transparent = imagecolorallocatealpha($crop, 0, 0, 0, 127);

		imagefill($crop, 0, 0, $transparent);

		imagecopy($crop, $this->imageResource, 0, 0, $x, $y, $oldWidth, $oldHeight);

		imagecolortransparent($crop, $transparent);

		$this->imageResource = $crop;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function flip(Flip $direction = Flip::HORIZONTAL): void
	{
		$width  = imagesx($this->imageResource);
		$height = imagesy($this->imageResource);

		$flipped = imagecreatetruecolor($width, $height);

		$transparent = imagecolorallocatealpha($flipped, 0, 0, 0, 127);

		imagefill($flipped, 0, 0, $transparent);

		if ($direction ===  Flip::VERTICAL) {
			for ($y = 0; $y < $height; $y++) {
				imagecopy($flipped, $this->imageResource, 0, $y, 0, $height - $y - 1, $width, 1);
			}
		}
		else {
			for ($x = 0; $x < $width; $x++) {
				imagecopy($flipped, $this->imageResource, $x, 0, $width - $x - 1, 0, 1, $height);
			}
		}

		imagecolortransparent($flipped, $transparent);

		$this->imageResource = $flipped;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function watermark(string $file, WatermarkPosition $position = WatermarkPosition::BOTTOM_RIGHT, int $opacity = 100): void
	{
		$watermark = $this->createImageResource($file);

		imagealphablending($watermark, false);
		imagesavealpha($watermark, true);

		$watermarkWidth  = imagesx($watermark);
		$watermarkHeight = imagesy($watermark);

		if ($opacity < 100) {
			// Convert opacity (0–100) to GD alpha (0–127)

			$opacityAlpha = 127 - round($opacity * 127 / 100);

			for ($x = 0; $x < $watermarkWidth; $x++) {
				for ($y = 0; $y < $watermarkHeight; $y++) {
					$rgba = imagecolorat($watermark, $x, $y);

					$r = ($rgba >> 16) & 0xFF;
					$g = ($rgba >> 8) & 0xFF;
					$b = $rgba & 0xFF;
					$a = ($rgba >> 24) & 0x7F;

					// Combine original alpha with desired opacity

					$newAlpha = min(127, $a + $opacityAlpha);

					$color = imagecolorallocatealpha($watermark, $r, $g, $b, $newAlpha);

					imagesetpixel($watermark, $x, $y, $color);
				}
			}
		}

		// Position the watermark

		switch ($position) {
			case WatermarkPosition::TOP_RIGHT:
				$x = imagesx($this->imageResource) - $watermarkWidth;
				$y = 0;
				break;
			case WatermarkPosition::BOTTOM_LEFT:
				$x = 0;
				$y = imagesy($this->imageResource) - $watermarkHeight;
				break;
			case WatermarkPosition::BOTTOM_RIGHT:
				$x = imagesx($this->imageResource) - $watermarkWidth;
				$y = imagesy($this->imageResource) - $watermarkHeight;
				break;
			case WatermarkPosition::CENTER:
				$x = (imagesx($this->imageResource) - $watermarkWidth) / 2;
				$y = (imagesy($this->imageResource) - $watermarkHeight) / 2;
				break;
			default:
				$x = 0;
				$y = 0;
		}

		imagealphablending($this->imageResource, true);
		imagesavealpha($this->imageResource, true);

		imagecopy($this->imageResource, $watermark, $x, $y, 0, 0, $watermarkWidth, $watermarkHeight);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function brightness(int $level = 50): void
	{
		$level *= 2.5;

		if ($this->hasFilters) {
			imagefilter($this->imageResource, IMG_FILTER_BRIGHTNESS, $level);
		}
		else {
			$width  = imagesx($this->imageResource);
			$height = imagesy($this->imageResource);

			$temp = imagecreatetruecolor($width, $height);

			// Adjust pixel brightness

			for ($x = 0; $x < $width; $x++) {
				for ($y = 0; $y < $height; $y++) {
					$rgb = imagecolorat($this->imageResource, $x, $y);

					$r = (($rgb >> 16) & 0xFF) + $level;
					$g = (($rgb >> 8) & 0xFF) + $level;
					$b = ($rgb & 0xFF) + $level;

					$r = ($r > 255) ? 255 : (($r < 0) ? 0 : $r);
					$g = ($g > 255) ? 255 : (($g < 0) ? 0 : $g);
					$b = ($b > 255) ? 255 : (($b < 0) ? 0 : $b);

					imagesetpixel($temp, $x, $y, imagecolorallocate($temp, $r, $g, $b));
				}
			}

			$this->imageResource = $temp;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function greyscale(): void
	{
		if ($this->hasFilters) {
			imagefilter($this->imageResource, IMG_FILTER_GRAYSCALE);
		}
		else {
			$width  = imagesx($this->imageResource);
			$height = imagesy($this->imageResource);

			$temp = imagecreatetruecolor($width, $height);

			// Generate array of shades of grey

			$greys = [];

			for ($i = 0; $i <= 255; $i++) {
				$greys[$i] = imagecolorallocate($temp, $i, $i, $i);
			}

			// Convert pixels to greyscale

			for ($x = 0; $x < $width; $x++) {
				for ($y = 0; $y < $height; $y++) {
					$rgb = imagecolorat($this->imageResource, $x, $y);

					$r = ($rgb >> 16) & 0xFF;
					$g = ($rgb >> 8) & 0xFF;
					$b = $rgb & 0xFF;

					imagesetpixel($temp, $x, $y, $greys[((0.299 * $r) + (0.587 * $g) + (0.114 * $b))]);
				}
			}

			$this->imageResource = $temp;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function sepia(): void
	{
		$width  = imagesx($this->imageResource);
		$height = imagesy($this->imageResource);

		$temp = imagecreatetruecolor($width, $height);

		// Convert pixels to sepia

		for ($x = 0; $x < $width; $x++) {
			for ($y = 0; $y < $height; $y++) {
				$rgb = imagecolorat($this->imageResource, $x, $y);

				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;

				$newR = ($r * 0.393 + $g * 0.769 + $b * 0.189) * 0.85;
				$newG = ($r * 0.349 + $g * 0.686 + $b * 0.168) * 0.85;
				$newB = ($r * 0.272 + $g * 0.534 + $b * 0.131) * 0.85;

				$newR = ($newR > 255) ? 255 : (($newR < 0) ? 0 : $newR);
				$newG = ($newG > 255) ? 255 : (($newG < 0) ? 0 : $newG);
				$newB = ($newB > 255) ? 255 : (($newB < 0) ? 0 : $newB);

				imagesetpixel($temp, $x, $y, imagecolorallocate($temp, $newR, $newG, $newB));
			}
		}

		$this->imageResource = $temp;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function bitonal(): void
	{
		if ($this->hasFilters) {
			imagefilter($this->imageResource, IMG_FILTER_GRAYSCALE);
			imagefilter($this->imageResource, IMG_FILTER_CONTRAST, -2000);
		}
		else {
			$width  = imagesx($this->imageResource);
			$height = imagesy($this->imageResource);

			$temp = imagecreatetruecolor($width, $height);

			// Colorize pixels

			for ($x = 0; $x < $width; $x++) {
				for ($y = 0; $y < $height; $y++) {
					$rgb = imagecolorat($this->imageResource, $x, $y);

					if ((((($rgb >> 16) & 0xFF) + (($rgb >> 8) & 0xFF) + ($rgb & 0xFF)) / 3) > 0x7F) {
						imagesetpixel($temp, $x, $y, imagecolorallocate($temp, 0xFF, 0xFF, 0xFF));
					}
					else {
						imagesetpixel($temp, $x, $y, imagecolorallocate($temp, 0, 0, 0));
					}
				}
			}

			$this->imageResource = $temp;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function colorize(Color $color): void
	{
		$colors = [
			'r' => $color->getRed(),
			'g' => $color->getGreen(),
			'b' => $color->getBlue(),
		];

		if ($this->hasFilters) {
			imagefilter($this->imageResource, IMG_FILTER_COLORIZE, $colors['r'], $colors['g'], $colors['b'], 0);
		}
		else {
			$width  = imagesx($this->imageResource);
			$height = imagesy($this->imageResource);

			$temp = imagecreatetruecolor($width, $height);

			for ($x = 0; $x < $width; $x++) {
				for ($y = 0; $y < $height; $y++) {
					$rgb = imagecolorat($this->imageResource, $x, $y);

					$r = (($rgb >> 16) & 0xFF) + $colors['r'];
					$g = (($rgb >> 8) & 0xFF) + $colors['g'];
					$b = ($rgb & 0xFF) + $colors['b'];

					$r = ($r > 255) ? 255 : (($r < 0) ? 0 : $r);
					$g = ($g > 255) ? 255 : (($g < 0) ? 0 : $g);
					$b = ($b > 255) ? 255 : (($b < 0) ? 0 : $b);

					imagesetpixel($temp, $x, $y, imagecolorallocate($temp, $r, $g, $b));
				}
			}

			$this->imageResource = $temp;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function sharpen(): void
	{
		$sharpen = [[-1.2, -1, -1.2], [-1, 20, -1], [-1.2, -1, -1.2]];

		$divisor = array_sum(array_map(array_sum(...), $sharpen));

		imageconvolution($this->imageResource, $sharpen, $divisor, 0);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function pixelate(int $pixelSize = 10): void
	{
		if ($this->hasFilters) {
			imagefilter($this->imageResource, IMG_FILTER_PIXELATE, $pixelSize, IMG_FILTER_PIXELATE);
		}
		else {
			$width  = imagesx($this->imageResource);
			$height = imagesy($this->imageResource);

			$this->resize((int) ($width / $pixelSize), (int) ($height / $pixelSize));

			$this->resize($width, $height);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function negate(): void
	{
		if ($this->hasFilters) {
			imagefilter($this->imageResource, IMG_FILTER_NEGATE);
		}
		else {
			$width  = imagesx($this->imageResource);
			$height = imagesy($this->imageResource);

			$temp = imagecreatetruecolor($width, $height);

			// Invert pixel colors

			for ($x = 0; $x < $width; $x++) {
				for ($y = 0; $y < $height; $y++) {
					imagesetpixel($temp, $x, $y, imagecolorat($this->imageResource, $x, $y) ^ 0x00FFFFFF);
				}
			}

			$this->imageResource = $temp;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function border(Color $color = new Color(0, 0, 0), int $thickness = 5): void
	{
		$width  = imagesx($this->imageResource);
		$height = imagesy($this->imageResource);

		$colors = [
			'r' => $color->getRed(),
			'g' => $color->getGreen(),
			'b' => $color->getBlue(),
		];

		$alhpa = 127 - (int) round($color->getAlpha() * 127 / 255);

		$color = imagecolorallocatealpha($this->imageResource, $colors['r'], $colors['g'], $colors['b'], $alhpa);

		for ($i = 0; $i < $thickness; $i++) {
			$x = --$width;
			$y = --$height;

			imagerectangle($this->imageResource, $i, $i, $x, $y, $color);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getTopColors(int $limit = 5, bool $ignoreTransparent = true): array
	{
		$step = 5;

		$width  = imagesx($this->imageResource);
		$height = imagesy($this->imageResource);

		$buckets = [];

		// Find colors

		for ($y = 0; $y < $height; $y += $step) {
			for ($x = 0; $x < $width; $x += $step) {
				$rgba = imagecolorat($this->imageResource, $x, $y);

				$alpha = 1 - ((($rgba & 0x7F000000) >> 24) / 127);

				if ($ignoreTransparent && $alpha < 0.1) {
					continue;
				}

				$r = (int) round((($rgba >> 16) & 0xFF) / 16) * 16;
				$g = (int) round((($rgba >> 8) & 0xFF) / 16) * 16;
				$b = (int) round(($rgba & 0xFF) / 16) * 16;

				$key = "$r,$g,$b,$alpha";

				$buckets[$key] = ($buckets[$key] ?? 0) + 1;
			}
		}

		// Sort colors

		arsort($buckets);

		// Collect and return the top n colors

		$colors = [];

		foreach (array_slice(array_keys($buckets), 0, $limit) as $rgba) {
			[$r, $g, $b, $a] = array_map(intval(...), explode(',', $rgba));

			$colors[] = new Color($r, $g, $b, $a * 255);
		}

		return $colors;
	}
}
