<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use mako\database\connections\Connection;
use mako\database\query\Query;
use Override;

use function serialize;
use function time;
use function unserialize;

/**
 * Database store.
 */
class Database extends Store
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
	public function put(string $key, mixed $data, int $ttl = 0): bool
	{
		$ttl = (((int) $ttl === 0) ? 31556926 : (int) $ttl) + time();

		$this->remove($key);

		return $this->table()->insert(['key' => $this->getPrefixedKey($key), 'data' => serialize($data), 'lifetime' => $ttl]);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function has(string $key): bool
	{
		return (bool) $this->table()->where('key', '=', $this->getPrefixedKey($key))->where('lifetime', '>', time())->count();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function get(string $key): mixed
	{
		$cache = $this->table()->where('key', '=', $this->getPrefixedKey($key))->first();

		if ($cache !== null) {
			if (time() < $cache->lifetime) {
				return unserialize($cache->data, ['allowed_classes' => $this->classWhitelist]);
			}

			$this->remove($key);
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function remove(string $key): bool
	{
		return (bool) $this->table()->where('key', '=', $this->getPrefixedKey($key))->delete();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function clear(): bool
	{
		$this->table()->delete();

		return true;
	}
}
