<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\password;

use mako\security\password\traits\ArgonTrait;
use Override;

/**
 * Argon2id hasher.
 */
class Argon2id extends Hasher
{
	use ArgonTrait;

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function getAlgorithm(): ?string
	{
		return PASSWORD_ARGON2ID;
	}
}
