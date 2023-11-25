<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\session\stores;

use SensitiveParameter;

/**
 * Null store.
 */
class NullStore implements StoreInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function write(#[SensitiveParameter] string $sessionId, array $sessionData, int $dataTTL): void
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function read(#[SensitiveParameter] string $sessionId): array
	{
		return [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(#[SensitiveParameter] string $sessionId): void
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function gc(int $dataTTL): void
	{

	}
}
