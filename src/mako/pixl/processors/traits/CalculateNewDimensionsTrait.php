<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixl\processors\traits;

use mako\pixl\Image;

use function min;
use function round;

/**
 * Calculate new image dimensions.
 */
trait CalculateNewDimensionsTrait
{
	/**
	 * Calculates new image dimensions.
	 */
	protected function calculateNewDimensions(int $width, ?int $height, int $oldWidth, int $oldHeight, int $aspectRatio): array
	{
		if ($height === null) {
			$newWidth  = round($oldWidth * ($width / 100));
			$newHeight = round($oldHeight * ($width / 100));
		}
		else {
			if ($aspectRatio === Image::RESIZE_AUTO) {
				// Calculate smallest size based on given height and width while maintaining aspect ratio

				$percentage = min(($width / $oldWidth), ($height / $oldHeight));

				$newWidth  = round($oldWidth * $percentage);
				$newHeight = round($oldHeight * $percentage);
			}
			elseif ($aspectRatio === Image::RESIZE_WIDTH) {
				// Base new size on given width while maintaining aspect ratio

				$newWidth  = $width;
				$newHeight = round($oldHeight * ($width / $oldWidth));
			}
			elseif ($aspectRatio === Image::RESIZE_HEIGHT) {
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
}
