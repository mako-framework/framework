<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\traits;

/**
 * Doesn't validate when empty trait.
 *
 * @author Frederic G. Østby
 */
trait DoesntValidateWhenEmptyTrait
{
	/**
	 * {@inheritdoc}
	 */
	public function validateWhenEmpty(): bool
	{
		return false;
	}
}
