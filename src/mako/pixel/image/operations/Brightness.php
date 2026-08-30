<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use mako\pixel\image\operations\traits\NormalizeTrait;

/**
 * Adjusts the image brightness.
 */
abstract class Brightness implements OperationInterface
{
	use NormalizeTrait;

	/**
	 * Constructor.
	 */
	final public function __construct(
		protected int $level
	) {
		$this->level = $this->normalizeLevel($level);
	}
}
