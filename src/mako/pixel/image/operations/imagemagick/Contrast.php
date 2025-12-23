<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\traits\NormalizeTrait;
use Override;

use function max;
use function min;

/**
 * Adjusts the image contrast.
 */
class Contrast implements OperationInterface
{
	use NormalizeTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $level = 0
	) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource, string $imagePath): void
	{
		if ($this->level === 0) {
			return;
		}

		$hasAlpha = $imageResource->getImageAlphaChannel();

		if ($hasAlpha) {
			$alpha = clone $imageResource;
		}

		$level = $this->normalizeLevel($this->level);

		$factor = 1 + (((100 + $level) / 100) - 1) * 0.8;

		$iterator = $imageResource->getPixelIterator();

		foreach ($iterator as $row => $pixels) {
			foreach ($pixels as $col => $pixel) {
				$colors = $pixel->getColor();

				$r = max(0, min(255, (($colors['r'] / 255 - 0.5) * $factor + 0.5) * 255));
				$g = max(0, min(255, (($colors['g'] / 255 - 0.5) * $factor + 0.5) * 255));
				$b = max(0, min(255, (($colors['b'] / 255 - 0.5) * $factor + 0.5) * 255));

				$pixel->setColor("rgb($r, $g, $b)");
			}

			$iterator->syncIterator();
		}

		$iterator->clear();
		$iterator->destroy();

		if ($hasAlpha) {
			$imageResource->compositeImage($alpha, Imagick::COMPOSITE_COPYOPACITY, 0, 0);

			$alpha->clear();
			$alpha->destroy();
		}
	}
}
