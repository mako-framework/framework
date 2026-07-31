<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\inspectors;

/**
 * Inspector interface.
 *
 * @template T
 */
interface InspectorInterface
{
	/**
	 * Inspects the image and returns the information.
	 *
	 * @return T
	 */
	public function inspect(object &$imageResource): mixed;
}
