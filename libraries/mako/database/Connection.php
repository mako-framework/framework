<?php

namespace mako\database;

use \mako\Config;
use \mako\Database;
use \mako\database\Query;
use \mako\database\query\Expression;
use \PDO;
use \PDOException;
use \RuntimeException;

/**
* Database connection.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Connection
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* PDO object.
	*
	* @var PDO
	*/

	public $pdo;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	* Constructor.
	*
	* @access  public
	* @param   array   Configuration
	*/

	public function __construct($name, array $config)
	{
		// Connect to the database

		$user = isset($config['username']) ? $config['username'] : null;
		$pass = isset($config['password']) ? $config['password'] : null;

		$options = array
		(
			PDO::ATTR_PERSISTENT         => isset($config['persistent']) ? $config['persistent'] : false,
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => Config::get('database.fetch_mode'),
			PDO::ATTR_STRINGIFY_FETCHES  => false,
		);

		try
		{
			$this->pdo = new PDO($config['dsn'], $user, $pass, $options);
		}
		catch(PDOException $e)
		{
			throw new RuntimeException(vsprintf("%s(): Failed to connect to the '%s' database. %s", array(__METHOD__, $name, $e->getMessage())));
		}

		// Run queries

		if(isset($config['queries']))
		{
			foreach($config['queries'] as $query)
			{
				$this->pdo->exec($query);
			}
		}
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	* Executes a query and returns the results.
	*
	* @access  public
	* @param   string  SQL query
	* @param   string  Query parameters
	* @return  mixed
	*/

	public function query($query, array $params = array(), $fetch = Database::FETCH_ALL)
	{
		// Replace IN clause placeholder with values

		if(strpos($query, '(...)') !== false)
		{
			foreach($params as $key => $value)
			{
				if(is_array($value))
				{
					foreach($value as $k => $v)
					{
						!is_numeric($v) && $value[$k] = $this->pdo->quote($v);
					}

					$query = preg_replace('/\(\.\.\.\)/', '(' . implode(',', $value) . ')', $query, 1);

					unset($params[$key]);
				}
			}
		}

		// Remove expressions from parameters

		$params = array_values(array_filter($params, function($param)
		{
			return ! $param instanceof Expression;
		}));

		// Prepare and execute query

		$stmt = $this->pdo->prepare($query);

		$result = $stmt->execute(array_values($params));

		// Return results for selects, row count for updates and deletes and boolean for the rest

		if(stripos($query, 'select') === 0)
		{
			switch($fetch)
			{
				case Database::FETCH:
					return $stmt->fetch();
				break;
				case Database::FETCH_COLUMN:
					return $stmt->fetchColumn();
				break;
				default:
					return $stmt->fetchAll();
			}
		}
		elseif(stripos($query, 'update') === 0 || stripos($query, 'delete') === 0)
		{
			return $stmt->rowCount();
		}
		else
		{
			return $result;
		}
	}

	/**
	* Returns an array containing all of the result set rows.
	*
	* @access  public
	* @param   string  SQL query
	* @param   string  Query parameters
	* @return  array
	*/

	public function all($query, array $params = array())
	{
		return $this->query($query, $params, Database::FETCH_ALL);
	}

	/**
	* Returns the first row of the result set.
	*
	* @access  public
	* @param   string  SQL query
	* @param   array   Query params
	* @return  mixed
	*/

	public function first($query, array $params = array())
	{
		return $this->query($query, $params, Database::FETCH);
	}

	/**
	* Returns the value of the first column of the first row of the result set.
	*
	* @access  public
	* @param   string  SQL query
	* @param   string  Query parameters
	* @return  mixed
	*/

	public function column($query, array $params = array())
	{
		return $this->query($query, $params, Database::FETCH_COLUMN);
	}

	/**
	*
	*/

	public function from($table)
	{
		return new Query($table, $this);
	}

	/**
	*
	*/

	public function to($table)
	{
		return $this->from($table);
	}

	/**
	* Performs calls on the pdo instance. This is mainly to contain backwards compatibility.
	*
	* @access  public
	* @param   string  Method name
	* @param   array   Method arguments
	* @return  mixed
	*/

	public function __call($name, $arguments)
	{
		return call_user_func_array(array($this->pdo, $name), $arguments);
	}
}

/** -------------------- End of file --------------------**/