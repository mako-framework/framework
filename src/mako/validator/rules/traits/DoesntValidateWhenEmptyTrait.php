<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\traits;

/**
 * Doesn't validate when empty trait.
 */
trait DoesntValidateWhenEmptyTrait
{
	/**
	 * Returns TRUE if the rule should be executed when the input is empty and FALSE if not.
	 */
	public function validateWhenEmpty(): bool
	{
		return false;
	}
}
