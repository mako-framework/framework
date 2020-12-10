<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\session\stores;

/**
 * Null store.
 */
class NullStore implements StoreInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function write(string $sessionId, array $sessionData, int $dataTTL): void
	{

	}

	/**
	 * {@inheritdoc}
	 */
	public function read(string $sessionId): array
	{
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(string $sessionId): void
	{

	}

	/**
	 * {@inheritdoc}
	 */
	public function gc(int $dataTTL): void
	{

	}
}
