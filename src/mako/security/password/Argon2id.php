<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\password;

/**
 * Argon2id hasher.
 */
class Argon2id extends Hasher
{
	/**
	 * {@inheritdoc}
	 */
	protected function getAlgorithm()
	{
		return PASSWORD_ARGON2ID;
	}
}
