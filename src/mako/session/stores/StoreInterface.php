<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\session\stores;

/**
 * Store interface.
 *
 * @author Frederic G. Østby
 */
interface StoreInterface
{
	/**
	 * Writes session data.
	 *
	 * @param string $sessionId   Session id
	 * @param array  $sessionData Session data
	 * @param int    $dataTTL     TTL in seconds
	 */
	public function write(string $sessionId, array $sessionData, int $dataTTL): void;

	/**
	 * Reads and returns session data.
	 *
	 * @param  string $sessionId Session id
	 * @return array
	 */
	public function read(string $sessionId): array;

	/**
	 * Destroys the session data assiciated with the provided id.
	 *
	 * @param string $sessionId Session id
	 */
	public function delete(string $sessionId): void;

	/**
	 * Garbage collector that deletes expired session data.
	 *
	 * @param int $dataTTL Data TTL in seconds
	 */
	public function gc(int $dataTTL): void;
}
