<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\session\stores;

use mako\database\connections\Connection;
use mako\database\query\Query;
use Override;
use SensitiveParameter;

use function serialize;
use function time;
use function unserialize;

/**
 * Database store.
 */
class Database implements StoreInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Connection $connection,
		protected string $table,
		protected array|bool $classWhitelist = false
	) {
	}

	/**
	 * Returns a query builder instance.
	 */
	protected function table(): Query
	{
		return $this->connection->getQuery()->table($this->table);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function write(#[SensitiveParameter] string $sessionId, array $sessionData, int $dataTTL): void
	{
		$sessionData = serialize($sessionData);

		if ($this->table()->where('id', '=', $sessionId)->count() != 0) {
			// Update existing session data

			$this->table()->where('id', '=', $sessionId)->update(['data' => $sessionData, 'expires' => (time() + $dataTTL)]);
		}
		else {
			// Insert new session data

			$this->table()->insert(['id' => $sessionId, 'data' => $sessionData, 'expires' => (time() + $dataTTL)]);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function read(#[SensitiveParameter] string $sessionId): array
	{
		$sessionData = $this->table()->select(['data'])->where('id', '=', $sessionId)->column();

		return ($sessionData !== null) ? unserialize($sessionData, ['allowed_classes' => $this->classWhitelist]) : [];
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function delete(#[SensitiveParameter] string $sessionId): void
	{
		$this->table()->where('id', '=', $sessionId)->delete();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function gc(int $dataTTL): void
	{
		$this->table()->where('expires', '<', time())->delete();
	}
}
