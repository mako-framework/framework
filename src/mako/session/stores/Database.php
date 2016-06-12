<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\session\stores;

use mako\database\connections\Connection;
use mako\session\stores\StoreInterface;

/**
 * Database store.
 *
 * @author  Frederic G. Østby
 */
class Database implements StoreInterface
{
	/**
	 * Database connection
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
	 * @var boolean|array
	 */
	protected $classWhitelist;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\database\connections\Connection  $connection      Database connection
	 * @param   string                                 $table           Database table
	 * @param   boolean|array                          $classWhitelist  Class whitelist
	 */
	public function __construct(Connection $connection, $table, $classWhitelist = false)
	{
		$this->connection = $connection;

		$this->table = $table;

		$this->classWhitelist = $classWhitelist;
	}

	/**
	 * Returns a query builder instance.
	 *
	 * @access  protected
	 * @return  \mako\database\query\Query
	 */
	protected function table()
	{
		return $this->connection->builder()->table($this->table);
	}

	/**
	 * {@inheritdoc}
	 */
	public function write($sessionId, $sessionData, $dataTTL)
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
	 * {@inheritdoc}
	 */
	public function read($sessionId)
	{
		$sessionData = $this->table()->select(['data'])->where('id', '=', $sessionId)->column();

		return ($sessionData !== false) ? unserialize($sessionData, ['allowed_classes' => $this->classWhitelist]) : [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete($sessionId)
	{
		$this->table()->where('id', '=', $sessionId)->delete();
	}

	/**
	 * {@inheritdoc}
	 */
	public function gc($dataTTL)
	{
		$this->table()->where('expires', '<', time())->delete();
	}
}