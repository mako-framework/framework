<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use InvalidArgumentException;
use mako\pixel\image\operations\OperationInterface;
use Override;

/**
 * Adjusts the gamma level of the image.
 */
class Gamma implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected float $gamma
	) {
		if ($gamma <= 0) {
			throw new InvalidArgumentException('Gamma must be greater than 0.');
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->gamma === 1.0) {
			return;
		}

		$imageResource->gammaImage($this->gamma);
	}
}
