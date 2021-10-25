<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\session\stores;

/**
 * Null store.
 *
 * @author Frederic G. Østby
 */
class NullStore implements StoreInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function write(string $sessionId, array $sessionData, int $dataTTL): void
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function read(string $sessionId): array
	{
		return [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(string $sessionId): void
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function gc(int $dataTTL): void
	{

	}
}
