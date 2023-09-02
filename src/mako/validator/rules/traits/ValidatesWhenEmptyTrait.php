<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\traits;

/**
 * Validates when empty trait.
 */
trait ValidatesWhenEmptyTrait
{
	/**
	 * Returns TRUE if the rule should be executed when the input is empty and FALSE if not.
	 */
	public function validateWhenEmpty(): bool
	{
		return true;
	}
}
