<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\traits;

use mako\pixel\image\operations\AspectRatio;

use function min;
use function round;

/**
 * Trait containing methods for calculating new dimensions.
 */
trait CalculateNewDimensionsTrait
{
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
}
