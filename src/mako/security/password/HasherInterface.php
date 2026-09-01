<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\password;

use SensitiveParameter;

/**
 * Hahser interface.
 */
interface HasherInterface
{
	/**
	 * Creates a password hash.
	 */
	public function create(#[SensitiveParameter] string $password): string;

	/**
	 * Verifies that the password matches the hash.
	 */
	public function verify(#[SensitiveParameter] string $password, string $hash): bool;

	/**
	 * Returns true if the password needs rehashing and false if not.
	 */
	public function needsRehash(string $hash): bool;
}
