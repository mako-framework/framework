<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\session\stores;

use mako\session\stores\StoreInterface;

/**
 * Null store.
 *
 * @author  Frederic G. Østby
 */
class NullStore implements StoreInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function write(string $sessionId, array $sessionData, int $dataTTL)
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
	public function delete(string $sessionId)
	{

	}

	/**
	 * {@inheritdoc}
	 */
	public function gc(int $dataTTL)
	{

	}
}
