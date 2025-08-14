<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\password;

use Override;

/**
 * Argon2i hasher.
 */
class Argon2i extends Hasher
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function getAlgorithm(): ?string
	{
		return PASSWORD_ARGON2I;
	}
}
