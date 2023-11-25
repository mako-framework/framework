<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\session\stores;

use SensitiveParameter;

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
		protected array|bool $classWhitelist = false,
		protected string $prefix = 'sess_'
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function write(#[SensitiveParameter] string $sessionId, array $sessionData, int $dataTTL): void
	{
		apcu_store("{$this->prefix}{$sessionId}", serialize($sessionData), $dataTTL);
	}

	/**
	 * {@inheritDoc}
	 */
	public function read(#[SensitiveParameter] string $sessionId): array
	{
		$sessionData = apcu_fetch("{$this->prefix}{$sessionId}");

		return ($sessionData !== false) ? unserialize($sessionData, ['allowed_classes' => $this->classWhitelist]) : [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(#[SensitiveParameter] string $sessionId): void
	{
		apcu_delete("{$this->prefix}{$sessionId}");
	}

	/**
	 * {@inheritDoc}
	 */
	public function gc(int $dataTTL): void
	{
		// Nothing here since APCu handles this automatically
	}
}
