<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use mako\database\connections\Connection;
use mako\database\query\Query;

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
	)
	{}

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
	public function put(string $key, mixed $data, int $ttl = 0): bool
	{
		$ttl = (((int) $ttl === 0) ? 31556926 : (int) $ttl) + time();

		$key = $this->getPrefixedKey($key);

		$this->remove($key);

		return $this->table()->insert(['key' => $key, 'data' => serialize($data), 'lifetime' => $ttl]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function has(string $key): bool
	{
		return (bool) $this->table()->where('key', '=', $this->getPrefixedKey($key))->where('lifetime', '>', time())->count();
	}

	/**
	 * {@inheritDoc}
	 */
	public function get(string $key): mixed
	{
		$key = $this->getPrefixedKey($key);

		$cache = $this->table()->where('key', '=', $key)->first();

		if($cache !== null)
		{
			if(time() < $cache->lifetime)
			{
				return unserialize($cache->data, ['allowed_classes' => $this->classWhitelist]);
			}

			$this->remove($key);
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function remove(string $key): bool
	{
		return (bool) $this->table()->where('key', '=', $this->getPrefixedKey($key))->delete();
	}

	/**
	 * {@inheritDoc}
	 */
	public function clear(): bool
	{
		$this->table()->delete();

		return true;
	}
}
