<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\traits\NormalizeTrait;
use Override;

use function abs;

/**
 * Adjusts the image brightness.
 */
class Brightness implements OperationInterface
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
	 * @param \Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource, string $imagePath): void
	{
		if ($this->level === 0) {
			return;
		}

		$level = $this->normalizeLevel($this->level);

		$imageResource->sigmoidalContrastImage($level > 0, abs($level) / 100 * 8, 0.5);
	}
}
