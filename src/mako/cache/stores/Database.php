<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores;

use mako\cache\stores\StoreInterface;
use mako\cache\stores\traits\GetOrElseTrait;
use mako\database\connections\Connection;

/**
 * Database store.
 *
 * @author  Frederic G. Østby
 */
class Database implements StoreInterface
{
	use GetOrElseTrait;

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
	public function put($key, $data, $ttl = 0)
	{
		$ttl = (((int) $ttl === 0) ? 31556926 : (int) $ttl) + time();

		$this->remove($key);

		return $this->table()->insert(['key' => $key, 'data' => serialize($data), 'lifetime' => $ttl]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function has($key)
	{
		return (bool) $this->table()->where('key', '=', $key)->where('lifetime', '>', time())->count();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($key)
	{
		$cache = $this->table()->where('key', '=', $key)->first();

		if($cache !== false)
		{
			if(time() < $cache->lifetime)
			{
				return unserialize($cache->data, ['allowed_classes' => $this->classWhitelist]);
			}

			$this->remove($key);
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove($key)
	{
		return (bool) $this->table()->where('key', '=', $key)->delete();
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear()
	{
		$this->table()->delete();

		return true;
	}
}