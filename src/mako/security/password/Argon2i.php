<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\password;

use mako\security\password\traits\Argon2Trait;
use Override;

/**
 * Argon2i hasher.
 */
class Argon2i extends Hasher
{
	use Argon2Trait;

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function getAlgorithm(): string
	{
		return PASSWORD_ARGON2I;
	}
}
