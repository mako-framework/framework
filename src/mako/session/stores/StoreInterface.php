<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\session\stores;

use SensitiveParameter;

/**
 * Store interface.
 */
interface StoreInterface
{
	/**
	 * Writes session data.
	 */
	public function write(#[SensitiveParameter] string $sessionId, array $sessionData, int $dataTTL): void;

	/**
	 * Reads and returns session data.
	 */
	public function read(#[SensitiveParameter] string $sessionId): array;

	/**
	 * Destroys the session data assiciated with the provided id.
	 */
	public function delete(#[SensitiveParameter] string $sessionId): void;

	/**
	 * Garbage collector that deletes expired session data.
	 */
	public function gc(int $dataTTL): void;
}
