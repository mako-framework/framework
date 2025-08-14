<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixl\processors;

use GdImage;
use mako\pixl\Image;
use mako\pixl\processors\exceptions\ProcessorException;
use mako\pixl\processors\traits\CalculateNewDimensionsTrait;
use Override;

use function abs;
use function array_map;
use function array_sum;
use function function_exists;
use function getimagesize;
use function hexdec;
use function imagealphablending;
use function imagecolorallocate;
use function imagecolorallocatealpha;
use function imagecolorat;
use function imagecolortransparent;
use function imageconvolution;
use function imagecopy;
use function imagecopyresampled;
use function imagecreatefromgif;
use function imagecreatefromjpeg;
use function imagecreatefrompng;
use function imagecreatetruecolor;
use function imagefill;
use function imagefilledrectangle;
use function imagefilter;
use function imagegif;
use function imagejpeg;
use function imagelayereffect;
use function imagepng;
use function imagerectangle;
use function imagerotate;
use function imagesavealpha;
use function imagesetpixel;
use function imagesx;
use function imagesy;
use function min;
use function ob_get_clean;
use function ob_start;
use function pathinfo;
use function preg_match;
use function round;
use function sprintf;
use function str_repeat;
use function str_replace;
use function strlen;
use function strtolower;
use function substr;

/**
 * GD processor.
 */
class GD implements ProcessorInterface
{
	use CalculateNewDimensionsTrait;

	/**
	 * Image resource.
	 */
	protected ?GdImage $image = null;

	/**
	 * Image resource.
	 */
	protected ?GdImage $snapshot = null;

	/**
	 * Image info.
	 */
	protected array $imageInfo = [];

	/**
	 * Do we have the imagefilter function?
	 */
	protected bool $hasFilters;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->hasFilters = function_exists('imagefilter');
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		$this->image = null;

		$this->snapshot = null;
	}

	/**
	 * Collects information about the image.
	 */
	protected function getImageInfo(string $file): array
	{
		$imageInfo = getimagesize($file);

		if ($imageInfo === false) {
			throw new ProcessorException(sprintf('Unable to process the image [ %s ].', $file));
		}

		return $imageInfo;
	}

	/**
	 * Creates an image resource that we can work with.
	 */
	protected function createImageResource(string $image, array $imageInfo): GdImage
	{
		return match ($imageInfo[2]) {
			IMAGETYPE_JPEG => imagecreatefromjpeg($image),
			IMAGETYPE_GIF  => imagecreatefromgif($image),
			IMAGETYPE_PNG  => imagecreatefrompng($image),
			default        => throw new ProcessorException(sprintf('Unable to open [ %s ]. Unsupported image type.', pathinfo($image, PATHINFO_EXTENSION))),
		};
	}

	/**
	 * Converts HEX value to an RGB array.
	 *
	 * @return array{r: int, g: int, b: int}
	 */
	protected function hexToRgb(string $hex): array
	{
		$hex = str_replace('#', '', $hex);

		if (preg_match('/^([a-f0-9]{3}){1,2}$/i', $hex) !== 1) {
			throw new ProcessorException(sprintf('Invalid HEX value [ %s ].', $hex));
		}

		if (strlen($hex) === 3) {
			$r = hexdec(str_repeat(substr($hex, 0, 1), 2));
			$g = hexdec(str_repeat(substr($hex, 1, 1), 2));
			$b = hexdec(str_repeat(substr($hex, 2, 1), 2));
		}
		else {
			$r = hexdec(substr($hex, 0, 2));
			$g = hexdec(substr($hex, 2, 2));
			$b = hexdec(substr($hex, 4, 2));
		}

		return ['r' => $r, 'g' => $g, 'b' => $b];
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function open(string $image): void
	{
		$this->imageInfo = $this->getImageInfo($image);

		$this->image = $this->createImageResource($image, $this->imageInfo);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function snapshot(): void
	{
		$width  = imagesx($this->image);
		$height = imagesy($this->image);

		$this->snapshot = imagecreatetruecolor($width, $height);

		imagecopy($this->snapshot, $this->image, 0, 0, 0, 0, $width, $height);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function restore(): void
	{
		if (($this->snapshot instanceof GdImage) === false) {
			throw new ProcessorException('No snapshot to restore.');
		}

		$this->image = $this->snapshot;

		$this->snapshot = null;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getWidth(): int
	{
		return imagesx($this->image);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getHeight(): int
	{
		return imagesy($this->image);
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
	public function rotate(int $degrees): void
	{
		$width  = imagesx($this->image);
		$height = imagesy($this->image);

		$transparent = imagecolorallocatealpha($this->image, 0, 0, 0, 127);

		if ($this->imageInfo[2] === IMAGETYPE_GIF) {
			$temp = imagecreatetruecolor($width, $height);

			imagefill($temp, 0, 0, $transparent);

			imagecopy($temp, $this->image, 0, 0, 0, 0, $width, $height);

			$this->image = $temp;
		}

		$this->image = imagerotate($this->image, (360 - $degrees), $transparent);

		imagecolortransparent($this->image, $transparent);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function resize(int $width, ?int $height = null, int $aspectRatio = Image::RESIZE_IGNORE): void
	{
		$oldWidth  = imagesx($this->image);
		$oldHeight = imagesy($this->image);

		[$newWidth, $newHeight] = $this->calculateNewDimensions($width, $height, $oldWidth, $oldHeight, $aspectRatio);

		$resized = imagecreatetruecolor($newWidth, $newHeight);

		$transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);

		imagefill($resized, 0, 0, $transparent);

		imagecopyresampled($resized, $this->image, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);

		imagecolortransparent($resized, $transparent);

		$this->image = $resized;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function crop(int $width, int $height, int $x, int $y): void
	{
		$oldWidth  = imagesx($this->image);
		$oldHeight = imagesy($this->image);

		$crop = imagecreatetruecolor($width, $height);

		$transparent = imagecolorallocatealpha($crop, 0, 0, 0, 127);

		imagefill($crop, 0, 0, $transparent);

		imagecopy($crop, $this->image, 0, 0, $x, $y, $oldWidth, $oldHeight);

		imagecolortransparent($crop, $transparent);

		$this->image = $crop;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function flip(int $direction = Image::FLIP_HORIZONTAL): void
	{
		$width  = imagesx($this->image);
		$height = imagesy($this->image);

		$flipped = imagecreatetruecolor($width, $height);

		$transparent = imagecolorallocatealpha($flipped, 0, 0, 0, 127);

		imagefill($flipped, 0, 0, $transparent);

		if ($direction ===  Image::FLIP_VERTICAL) {
			// Flips the image in the vertical direction

			for ($y = 0; $y < $height; $y++) {
				imagecopy($flipped, $this->image, 0, $y, 0, $height - $y - 1, $width, 1);
			}
		}
		else {
			// Flips the image in the horizontal direction

			for ($x = 0; $x < $width; $x++) {
				imagecopy($flipped, $this->image, $x, 0, $width - $x - 1, 0, 1, $height);
			}
		}

		imagecolortransparent($flipped, $transparent);

		$this->image = $flipped;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function watermark(string $file, int $position = Image::WATERMARK_TOP_LEFT, int $opacity = 100): void
	{
		$watermark = $this->createImageResource($file, $this->getImageInfo($file));

		$watermarkWidth  = imagesx($watermark);
		$watermarkHeight = imagesy($watermark);

		if ($opacity < 100) {
			// Convert alpha to 0-127

			$alpha = min(round(abs(($opacity * 127 / 100) - 127)), 127);

			$transparent = imagecolorallocatealpha($watermark, 0, 0, 0, $alpha);

			imagelayereffect($watermark, IMG_EFFECT_OVERLAY);

			imagefilledrectangle($watermark, 0, 0, $watermarkWidth, $watermarkHeight, $transparent);
		}

		// Position the watermark.

		switch ($position) {
			case Image::WATERMARK_TOP_RIGHT:
				$x = imagesx($this->image) - $watermarkWidth;
				$y = 0;
				break;
			case Image::WATERMARK_BOTTOM_LEFT:
				$x = 0;
				$y = imagesy($this->image) - $watermarkHeight;
				break;
			case Image::WATERMARK_BOTTOM_RIGHT:
				$x = imagesx($this->image) - $watermarkWidth;
				$y = imagesy($this->image) - $watermarkHeight;
				break;
			case Image::WATERMARK_CENTER:
				$x = (imagesx($this->image) / 2) - ($watermarkWidth / 2);
				$y = (imagesy($this->image) / 2) - ($watermarkHeight / 2);
				break;
			default:
				$x = 0;
				$y = 0;
		}

		imagealphablending($this->image, true);

		imagecopy($this->image, $watermark, $x, $y, 0, 0, $watermarkWidth, $watermarkHeight);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function brightness(int $level = 50): void
	{
		$level *= 2.5;

		if ($this->hasFilters) {
			imagefilter($this->image, IMG_FILTER_BRIGHTNESS, $level);
		}
		else {
			$width  = imagesx($this->image);
			$height = imagesy($this->image);

			$temp = imagecreatetruecolor($width, $height);

			// Adjust pixel brightness

			for ($x = 0; $x < $width; $x++) {
				for ($y = 0; $y < $height; $y++) {
					$rgb = imagecolorat($this->image, $x, $y);

					$r = (($rgb >> 16) & 0xFF) + $level;
					$g = (($rgb >> 8) & 0xFF) + $level;
					$b = ($rgb & 0xFF) + $level;

					$r = ($r > 255) ? 255 : (($r < 0) ? 0 : $r);
					$g = ($g > 255) ? 255 : (($g < 0) ? 0 : $g);
					$b = ($b > 255) ? 255 : (($b < 0) ? 0 : $b);

					imagesetpixel($temp, $x, $y, imagecolorallocate($temp, $r, $g, $b));
				}
			}

			$this->image = $temp;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function greyscale(): void
	{
		if ($this->hasFilters) {
			imagefilter($this->image, IMG_FILTER_GRAYSCALE);
		}
		else {
			$width  = imagesx($this->image);
			$height = imagesy($this->image);

			$temp = imagecreatetruecolor($width, $height);

			// Generate array of shades of grey

			$greys = [];

			for ($i = 0; $i <= 255; $i++) {
				$greys[$i] = imagecolorallocate($temp, $i, $i, $i);
			}

			// Convert pixels to greyscale

			for ($x = 0; $x < $width; $x++) {
				for ($y = 0; $y < $height; $y++) {
					$rgb = imagecolorat($this->image, $x, $y);

					$r = ($rgb >> 16) & 0xFF;
					$g = ($rgb >> 8) & 0xFF;
					$b = $rgb & 0xFF;

					imagesetpixel($temp, $x, $y, $greys[((0.299 * $r) + (0.587 * $g) + (0.114 * $b))]);
				}
			}

			$this->image = $temp;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function sepia(): void
	{
		$width  = imagesx($this->image);
		$height = imagesy($this->image);

		$temp = imagecreatetruecolor($width, $height);

		// Convert pixels to sepia

		for ($x = 0; $x < $width; $x++) {
			for ($y = 0; $y < $height; $y++) {
				$rgb = imagecolorat($this->image, $x, $y);

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

		$this->image = $temp;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function bitonal(): void
	{
		if ($this->hasFilters) {
			imagefilter($this->image, IMG_FILTER_GRAYSCALE);
			imagefilter($this->image, IMG_FILTER_CONTRAST, -2000);
		}
		else {
			$width  = imagesx($this->image);
			$height = imagesy($this->image);

			$temp = imagecreatetruecolor($width, $height);

			// Colorize pixels

			for ($x = 0; $x < $width; $x++) {
				for ($y = 0; $y < $height; $y++) {
					$rgb = imagecolorat($this->image, $x, $y);

					if ((((($rgb >> 16) & 0xFF) + (($rgb >> 8) & 0xFF) + ($rgb & 0xFF)) / 3) > 0x7F) {
						imagesetpixel($temp, $x, $y, imagecolorallocate($temp, 0xFF, 0xFF, 0xFF));
					}
					else {
						imagesetpixel($temp, $x, $y, imagecolorallocate($temp, 0, 0, 0));
					}
				}
			}

			$this->image = $temp;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function colorize(string $color): void
	{
		$rgb = $this->hexToRgb($color);

		if ($this->hasFilters) {
			imagefilter($this->image, IMG_FILTER_COLORIZE, $rgb['r'], $rgb['g'], $rgb['b'], 0);
		}
		else {
			$colorize = $rgb;

			$width  = imagesx($this->image);
			$height = imagesy($this->image);

			$temp = imagecreatetruecolor($width, $height);

			// Colorize pixels

			for ($x = 0; $x < $width; $x++) {
				for ($y = 0; $y < $height; $y++) {
					$rgb = imagecolorat($this->image, $x, $y);

					$r = (($rgb >> 16) & 0xFF) + $colorize['r'];
					$g = (($rgb >> 8) & 0xFF) + $colorize['g'];
					$b = ($rgb & 0xFF) + $colorize['b'];

					$r = ($r > 255) ? 255 : (($r < 0) ? 0 : $r);
					$g = ($g > 255) ? 255 : (($g < 0) ? 0 : $g);
					$b = ($b > 255) ? 255 : (($b < 0) ? 0 : $b);

					imagesetpixel($temp, $x, $y, imagecolorallocate($temp, $r, $g, $b));
				}
			}

			$this->image = $temp;
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

		imageconvolution($this->image, $sharpen, $divisor, 0);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function pixelate(int $pixelSize = 10): void
	{
		if ($this->hasFilters) {
			imagefilter($this->image, IMG_FILTER_PIXELATE, $pixelSize, IMG_FILTER_PIXELATE);
		}
		else {
			$width  = imagesx($this->image);
			$height = imagesy($this->image);

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
			imagefilter($this->image, IMG_FILTER_NEGATE);
		}
		else {
			$width  = imagesx($this->image);
			$height = imagesy($this->image);

			$temp = imagecreatetruecolor($width, $height);

			// Invert pixel colors

			for ($x = 0; $x < $width; $x++) {
				for ($y = 0; $y < $height; $y++) {
					imagesetpixel($temp, $x, $y, imagecolorat($this->image, $x, $y) ^ 0x00FFFFFF);
				}
			}

			$this->image = $temp;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function border(string $color = '#000', int $thickness = 5): void
	{
		$width  = imagesx($this->image);
		$height = imagesy($this->image);

		$rgb = $this->hexToRgb($color);

		$color = imagecolorallocatealpha($this->image, $rgb['r'], $rgb['g'], $rgb['b'], 0);

		for ($i = 0; $i < $thickness; $i++) {
			$x = --$width;
			$y = --$height;

			imagerectangle($this->image, $i, $i, $x, $y, $color);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getImageBlob(?string $type = null, int $quality = 95): string
	{
		$type ??= $this->imageInfo['mime'];

		// Return image blob

		ob_start();

		switch ($type) {
			case 'gif':
			case 'image/gif':
				imagegif($this->image);
				break;
			case 'jpg':
			case 'jpeg':
			case 'image/jpeg':
				imagejpeg($this->image, quality: $quality);
				break;
			case 'png':
			case 'image/png':
				imagealphablending($this->image, true);
				imagesavealpha($this->image, true);
				imagepng($this->image, quality: (int) (9 - (round(($quality / 100) * 9))));
				break;
			default:
				throw new ProcessorException(sprintf('Unsupported image type [ %s ].', $type));
		}

		return ob_get_clean();

	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function save(string $file, int $quality = 95): void
	{
		// Get the file extension

		$extension = pathinfo($file, PATHINFO_EXTENSION);

		// Save image

		switch (strtolower($extension)) {
			case 'gif':
				imagegif($this->image, $file);
				break;
			case 'jpg':
			case 'jpeg':
				imagejpeg($this->image, $file, $quality);
				break;
			case 'png':
				imagealphablending($this->image, true);
				imagesavealpha($this->image, true);
				imagepng($this->image, $file, (int) (9 - (round(($quality / 100) * 9))));
				break;
			default:
				throw new ProcessorException(sprintf('Unable to save as [ %s ]. Unsupported image format.', $extension));
		}
	}
}
