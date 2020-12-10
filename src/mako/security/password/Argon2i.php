<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\password;

/**
 * Argon2i hasher.
 */
class Argon2i extends Hasher
{
	/**
	 * {@inheritdoc}
	 */
	protected function getAlgorithm()
	{
		return PASSWORD_ARGON2I;
	}
}
