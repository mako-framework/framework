<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\session\stores;

use function apcu_delete;
use function apcu_fetch;
use function apcu_store;
use function serialize;
use function unserialize;

/**
 * APCu store.
 */
class APCu implements StoreInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected array|bool $classWhitelist = false
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function write(string $sessionId, array $sessionData, int $dataTTL): void
	{
		apcu_store("sess_{$sessionId}", serialize($sessionData), $dataTTL);
	}

	/**
	 * {@inheritDoc}
	 */
	public function read(string $sessionId): array
	{
		$sessionData = apcu_fetch("sess_{$sessionId}");

		return ($sessionData !== false) ? unserialize($sessionData, ['allowed_classes' => $this->classWhitelist]) : [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(string $sessionId): void
	{
		apcu_delete("sess_{$sessionId}");
	}

	/**
	 * {@inheritDoc}
	 */
	public function gc(int $dataTTL): void
	{
		// Nothing here since APCu handles this automatically
	}
}
