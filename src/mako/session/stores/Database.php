<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\session\stores;

use mako\database\connections\Connection;
use mako\database\query\Query;

use function serialize;
use function time;
use function unserialize;

/**
 * Database store.
 */
class Database implements StoreInterface
{
	/**
	 * Database connection.
	 *
	 * @var \mako\database\connections\Connection
	 */
	protected $connection;

	/**
	 * Database table.
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * Class whitelist.
	 *
	 * @var array|bool
	 */
	protected $classWhitelist;

	/**
	 * Constructor.
	 *
	 * @param \mako\database\connections\Connection $connection     Database connection
	 * @param string                                $table          Database table
	 * @param array|bool                            $classWhitelist Class whitelist
	 */
	public function __construct(Connection $connection, string $table, $classWhitelist = false)
	{
		$this->connection = $connection;

		$this->table = $table;

		$this->classWhitelist = $classWhitelist;
	}

	/**
	 * Returns a query builder instance.
	 *
	 * @return \mako\database\query\Query
	 */
	protected function table(): Query
	{
		return $this->connection->builder()->table($this->table);
	}

	/**
	 * {@inheritDoc}
	 */
	public function write(string $sessionId, array $sessionData, int $dataTTL): void
	{
		$sessionData = serialize($sessionData);

		if($this->table()->where('id', '=', $sessionId)->count() != 0)
		{
			// Update existing session data

			$this->table()->where('id', '=', $sessionId)->update(['data' => $sessionData, 'expires' => (time() + $dataTTL)]);
		}
		else
		{
			// Insert new session data

			$this->table()->insert(['id' => $sessionId, 'data' => $sessionData, 'expires' => (time() + $dataTTL)]);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function read(string $sessionId): array
	{
		$sessionData = $this->table()->select(['data'])->where('id', '=', $sessionId)->column();

		return ($sessionData !== null) ? unserialize($sessionData, ['allowed_classes' => $this->classWhitelist]) : [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete(string $sessionId): void
	{
		$this->table()->where('id', '=', $sessionId)->delete();
	}

	/**
	 * {@inheritDoc}
	 */
	public function gc(int $dataTTL): void
	{
		$this->table()->where('expires', '<', time())->delete();
	}
}
