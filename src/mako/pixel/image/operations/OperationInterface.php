<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

/**
 * Operation interface.
 */
interface OperationInterface
{
	/**
	 * Applies the operation on the image resource.
	 */
	public function apply(object &$imageResource): void;
}
