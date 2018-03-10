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
	 * {@inheritdoc}
	 */
	public function validateWhenEmpty(): bool
	{
		return true;
	}
}
