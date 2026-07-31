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
	protected function calculateNewDimensions(int $width, int $height, int $oldWidth, int $oldHeight, AspectRatio $aspectRatio): array
	{
		if ($aspectRatio === AspectRatio::Auto) {
			// Calculate smallest size based on given height and width while maintaining aspect ratio

			$percentage = min(($width / $oldWidth), ($height / $oldHeight));

			$newWidth  = (int) round($oldWidth * $percentage);
			$newHeight = (int) round($oldHeight * $percentage);
		}
		elseif ($aspectRatio === AspectRatio::Width) {
			// Base new size on given width while maintaining aspect ratio

			$newWidth  = $width;
			$newHeight = (int) round($oldHeight * ($width / $oldWidth));
		}
		elseif ($aspectRatio === AspectRatio::Height) {
			// Base new size on given height while maintaining aspect ratio

			$newWidth  = (int) round($oldWidth * ($height / $oldHeight));
			$newHeight = $height;
		}
		else {
			// Ignone aspect ratio

			$newWidth  = $width;
			$newHeight = $height;
		}

		return [$newWidth, $newHeight];
	}
}
