<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\traits;

/**
 * Validates when empty trait.
 *
 * @author Frederic G. Østby
 */
trait ValidatesWhenEmptyTrait
{
	/**
	 * Returns TRUE if the rule should be executed when the input is empty and FALSE if not.
	 *
	 * @return bool
	 */
	public function validateWhenEmpty(): bool
	{
		return true;
	}
}
