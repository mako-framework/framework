<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\session\stores;

use Override;
use SensitiveParameter;

/**
 * Null store.
 */
class NullStore implements StoreInterface
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function write(#[SensitiveParameter] string $sessionId, array $sessionData, int $dataTTL): void
	{

	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function read(#[SensitiveParameter] string $sessionId): array
	{
		return [];
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function delete(#[SensitiveParameter] string $sessionId): void
	{

	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function gc(int $dataTTL): void
	{

	}
}
