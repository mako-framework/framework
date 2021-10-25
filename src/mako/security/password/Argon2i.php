<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\password;

/**
 * Argon2i hasher.
 *
 * @author Frederic G. Østby
 */
class Argon2i extends Hasher
{
	/**
	 * {@inheritDoc}
	 */
	protected function getAlgorithm()
	{
		return PASSWORD_ARGON2I;
	}
}
