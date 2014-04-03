<?php

namespace mako\database\midgard;

use \RuntimeException;

use \mako\core\Application;
use \mako\database\ConnectionManager;
use \mako\database\midgard\Hydrator;
use \mako\database\midgard\relations\BelongsTo;
use \mako\database\midgard\relations\HasMany;
use \mako\database\midgard\relations\HasOne;
use \mako\database\midgard\relations\ManyToMany;
use \mako\database\midgard\StaleRecordException;
use \mako\utility\Str;
use \mako\utility\UUID;

/**
 * ORM.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

abstract class ORM
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Incrementing primary key.
	 * 
	 * @var int
	 */

	const PRIMARY_KEY_TYPE_INCREMENTING = 1000;

	/**
	 * UUID primary key.
	 * 
	 * @var int
	 */

	const PRIMARY_KEY_TYPE_UUID = 1001;

	/**
	 * Custom primary key.
	 * 
	 * @var int
	 */

	const PRIMARY_KEY_TYPE_CUSTOM = 1002;

	/**
	 * No primary key.
	 * 
	 * @var int
	 */

	const PRIMARY_KEY_TYPE_NONE = 1003;

	/**
	 * Connection name to use for the model.
	 * 
	 * @var string
	 */

	protected $connectionName = null;

	/**
	 * Connection manager instance.
	 * 
	 * @var \mako\database\ConnectionManager
	 */

	protected static $connectionManager = null;

	/**
	 * Table name.
	 * 
	 * @var string
	 */

	protected $tableName = null;

	/**
	 * Primary key.
	 * 
	 * @var string
	 */

	protected $primaryKey = 'id';

	/**
	 * Does this table have an auto increment primary index?
	 * 
	 * @var boolean
	 */

	protected $primaryKeyType = ORM::PRIMARY_KEY_TYPE_INCREMENTING;

	/**
	 * Enable optimistic locking?
	 * 
	 * @var boolean
	 */

	protected $enableLocking = false;

	/**
	 * Optimistic locking column.
	 * 
	 * @var string
	 */

	protected $lockingColumn = 'lock_version';

	/**
	 * Has the record been loaded from a database?
	 * 
	 * @var boolean
	 */

	protected $exists = false;

	/**
	 * Is this a read only record?
	 * 
	 * @var boolean
	 */

	protected $readOnly = false;

	/**
	 * Column values.
	 * 
	 * @var array
	 */

	protected $columns = [];

	/**
	 * Original column values.
	 * 
	 * @var array
	 */

	protected $original = [];

	/**
	 * Relations to eager load.
	 * 
	 * @var array
	 */

	protected $including = [];

	/**
	 * Related records.
	 * 
	 * @var array
	 */

	protected $related = [];

	/**
	 * Columns that can be set through mass assignment.
	 * 
	 * @var array
	 */

	protected $assignable = [];

	/**
	 * Columns that are excluded from the array and json representations of the record.
	 * 
	 * @var array
	 */

	protected $protected = [];

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   array    $colums     Column values
	 * @param   boolean  $raw        (optional) Set raw values?
	 * @param   boolean  $whitelist  (optional) Remove columns that are not in the whitelist?
	 * @param   boolean  $exists     (optional) Does the record come from a database?
	 * @param   boolean  $readOnly   (optional) Is this a read-only record?
	 */

	public function __construct(array $columns = [], $raw = false, $whitelist = true, $exists = false, $readOnly = false)
	{
		$this->assign($columns, $raw, $whitelist);

		if($exists)
		{
			$this->original = $this->columns;

			$this->exists = true;

			$this->readOnly = $this->readOnly || $readOnly;
		}
	}

	/**
	 * Making sure that cloning returns a "fresh copy" of the record.
	 * 
	 * @access  public
	 */

	public function __clone()
	{
		if($this->exists)
		{
			$this->exists   = false;
			$this->original = [];
			$this->related  = [];

			unset($this->columns[$this->primaryKey]);

			if($this->enableLocking)
			{
				unset($this->columns[$this->lockingColumn]);
			}
		}
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Set the connection manager.
	 * 
	 * @access  public
	 * @param   \mako\database\ConnectionManager  $connectionManager  Connection manager instance
	 */

	public static function setConnectionManager(ConnectionManager $connectionManager)
	{
		static::$connectionManager = $connectionManager;
	}

	/**
	 * Returns the connection of the model.
	 * 
	 * @access  public
	 * @return  \mako\database\Connection
	 */

	public function getConnection()
	{
		if(empty(static::$connectionManager))
		{
			static::$connectionManager = Application::instance()->get('database');
		}
		
		return static::$connectionManager->connection($this->connectionName);
	}

	/**
	 * Returns the table name of the model.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getTable()
	{
		if($this->tableName === null)
		{
			throw new RuntimeException(vsprintf("%s(): You need to define the table name.", [__METHOD__, get_class($this)]));
		}
		
		return $this->tableName;
	}

	/**
	 * Returns the primary key of the table.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getPrimaryKey()
	{
		return $this->primaryKey;
	}

	/**
	 * Returns the primary key value.
	 * 
	 * @access  public
	 * @return  mixed
	 */

	public function getPrimaryKeyValue()
	{
		return $this->columns[$this->primaryKey];
	}

	/**
	 * Returns the foreign key of the table.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getForeignKey()
	{
		return strtolower(end((explode('\\', get_class($this))))) . '_id';
	}

	/**
	 * Returns the namespaced class name of the model.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getClass()
	{
		return '\\' . get_class($this);
	}

	/**
	 * Sets the optimistic locking version.
	 * 
	 * @access  public
	 * @param   int     $version  Locking version
	 */

	public function setLockVersion($version)
	{
		if($this->enableLocking)
		{
			$this->columns[$this->lockingColumn] = $version;
		}
	}

	/**
	 * Returns the optimistic locking version.
	 * 
	 * @access  public
	 * @return  int
	 */

	public function getLockVersion()
	{
		if($this->enableLocking && $this->exists)
		{
			return $this->columns[$this->lockingColumn];
		}

		return false;
	}

	/**
	 * Is this a read-only record?
	 * 
	 * @access  public
	 * @return  boolean
	 */

	public function isReadOnly()
	{
		return $this->readOnly;
	}

	/**
	 * Sets the relations to eager load.
	 * 
	 * @access  public
	 * @param   array   $includes  Relations to eager load
	 */

	public function setIncludes(array $includes)
	{
		$this->including = $includes;
	}

	/**
	 * Returns the relations to eager load.
	 * 
	 * @access  public
	 * @return  array
	 */

	public function getIncludes()
	{
		return $this->including;
	}

	/**
	 * Sets eagerly loaded related records.
	 * 
	 * @access  public
	 * @param   string  $relation  Relation name
	 * @param   mixed   $related   Related record(s)
	 */

	public function setRelated($relation, $related)
	{
		$this->related[$relation] = $related;
	}

	/**
	 * Returns the columns array.
	 * 
	 * @access  public
	 * @return  array
	 */

	public function getColumns()
	{
		return $this->columns;
	}

	/**
	 * Returns the related records array.
	 * 
	 * @access  public
	 * @return  array
	 */

	public function getRelated()
	{
		return $this->related;
	}

	/**
	 * Sets a column value.
	 * 
	 * @access  public
	 * @param   string   $name   Column name
	 * @param   mixed    $value  Column value
	 * @param   boolean  $raw    (optional) Set raw value?
	 */

	public function setColumn($name, $value, $raw = false)
	{
		if(!$raw && method_exists($this, 'set_' . $name))
		{
			// The column has a setter

			$this->columns[$name] = $this->{'set_' . $name}($value);
		}
		else
		{
			// Just set the raw column value
			
			$this->columns[$name] = $value;
		}
	}

	/**
	 * Gets a column value.
	 * 
	 * @access  public
	 * @param   string   $name  Column name
	 * @param   boolean  $raw   (optional) Return raw value?
	 * @return  mixed
	 */

	public function getColumn($name, $raw = false)
	{
		if(isset($this->related[$name]))
		{
			// The column is a cached or eagerly loaded relation

			return $this->related[$name];
		}
		elseif(method_exists($this, $name))
		{
			// The column is a relation. Lazy load the records and cache them

			return $this->related[$name] = $this->{$name}()->getRelated();
		}
		elseif(!$raw && method_exists($this, 'get_' . $name))
		{
			// The column has a getter

			return $this->{'get_' . $name}(isset($this->columns[$name]) ? $this->columns[$name] : null);
		}
		else
		{
			// Just a normal column

			return $this->columns[$name];
		}
	}

	/**
	 * Assigns the column values to the mode.
	 * 
	 * @access  public
	 * @param   array                       $columns    Column values
	 * @param   boolean                     $raw        (optional) Set raw values?
	 * @param   boolean                     $whitelist  (optional) Remove columns that are not in the whitelist?
	 * @return  \mako\database\midgard\ORM
	 */

	public function assign(array $columns, $raw = false, $whitelist = true)
	{
		// Remove columns that are not in the whitelist

		if($whitelist && !empty($this->assignable))
		{
			$columns = array_intersect_key($columns, array_flip($this->assignable));
		}

		// Remove the primary key if the model has already beed loaded

		if($this->exists && isset($columns[$this->primaryKey]))
		{
			unset($columns[$this->primaryKey]);
		}

		// Set column values

		foreach($columns as $column => $value)
		{
			$this->setColumn($column, $value, $raw);
		}

		return $this;
	}

	/**
	 * Set column value using overloading.
	 * 
	 * @access  public
	 * @param   string  $name   Column name
	 * @param   mixed   $value  Column value
	 */

	public function __set($name, $value)
	{
		$this->setColumn($name, $value);
	}

	/**
	 * Get column value using overloading.
	 * 
	 * @access  public
	 * @param   string  $name  Column name
	 * @return  mixed
	 */

	public function __get($name)
	{
		return $this->getColumn($name);
	}

	/**
	 * Checks if a column or relation is set using overloading.
	 * 
	 * @access  public
	 * @param   string  $name  Column name
	 */

	public function __isset($name)
	{
		return isset($this->columns[$name]) || isset($this->related[$name]);
	}

	/**
	 * Unset column value or relation using overloading.
	 * 
	 * @access  public
	 * @param   string  $name  Column name
	 */

	public function __unset($name)
	{
		unset($this->columns[$name], $this->related[$name]);
	}

	/**
	 * Returns a hydrator instance.
	 * 
	 * @access  protected
	 * @return  \mako\database\midgard\Hydrator
	 */

	protected function hydrator()
	{
		return new Hydrator($this->getConnection(), $this);
	}

	/**
	 * Returns a record using the value of its primary key.
	 * 
	 * @access  public
	 * @param   int                         $id       Primary key
	 * @param   array                       $columns  (optional) Columns to select
	 * @return  \mako\database\midgard\ORM
	 */

	public static function get($id, array $columns = [])
	{
		return (new static)->hydrator()->get($id, $columns);
	}

	/**
	 * Reloads the record from the database.
	 * 
	 * @access  public
	 * @return  boolean
	 */

	public function reload()
	{
		if($this->exists)
		{
			$model = static::get($this->getPrimaryKeyValue());

			if($model !== false)
			{
				$this->original = $this->columns = $model->getColumns();

				$this->related = $model->getRelated();

				return true;
			}
		}

		return false;
	}

	/**
	 * Creates a new record and returns the model.
	 * 
	 * @access  public
	 * @param   array                       $columns    Column values
	 * @param   boolean                     $raw        (optional) Set raw values?
	 * @param   boolean                     $whitelsit  (optional) Remove columns that are not in the whitelist?
	 * @return  \mako\database\midgard\ORM
	 */

	public static function create(array $columns, $raw = false, $whitelist = true)
	{
		$model = new static($columns, $raw, $whitelist);

		$model->save();

		return $model;
	}

	/**
	 * Returns a HasOne relation.
	 * 
	 * @access  protected
	 * @param   string                                  $model       Related model
	 * @param   string|null                             $foreignKey  (optional) Foreign key name
	 * @return  \mako\database\midgard\relation\HasOne
	 */

	protected function hasOne($model, $foreignKey = null)
	{
		$related = new $model;

		return new HasOne($related->getConnection(), $this, $related, $foreignKey);
	}

	/**
	 * Returns a HasMany relation.
	 * 
	 * @access  protected
	 * @param   string                                   $model       Related model
	 * @param   string|null                              $foreignKey  (optional) Foreign key name
	 * @return  \mako\database\midgard\relation\HasMany
	 */

	protected function hasMany($model, $foreignKey = null)
	{
		$related = new $model;

		return new HasMany($related->getConnection(), $this, $related, $foreignKey);
	}

	/**
	 * Returns a ManyToMany relation.
	 * 
	 * @access  protected
	 * @param   string                                      $model          Related model
	 * @param   string|null                                 $foreignKey     (optional) Foreign key name
	 * @param   string|null                                 $junctionTable  (optional) Junction table name
	 * @param   string|null                                 $junctionKey    (optional) Junction key name
	 * @return  \mako\database\midgard\relation\ManyToMany
	 */

	protected function manyToMany($model, $foreignKey = null, $junctionTable = null, $junctionKey = null)
	{
		$related = new $model;

		return new ManyToMany($related->getConnection(), $this, $related, $foreignKey, $junctionTable, $junctionKey);
	}

	/**
	 * Returns a BelongsTo relation.
	 * 
	 * @access  protected
	 * @param   string                                     $model       Related model
	 * @param   string|null                                $foreignKey  (optional) Foreign key name
	 * @return  \mako\database\midgard\relation\BelongsTo
	 */

	protected function belongsTo($model, $foreignKey = null)
	{
		$related = new $model;

		return new BelongsTo($related->getConnection(), $this, $related, $foreignKey);
	}

	/**
	 * Has the record been modified?
	 * 
	 * @access  protected
	 * @return  boolean
	 */

	protected function isModified()
	{
		return count($this->getModified()) > 0;
	}

	/**
	 * Returns the modified column values of the record.
	 * 
	 * @access  protected
	 * @return  array
	 */

	protected function getModified()
	{
		$modified = [];

		foreach($this->columns as $key => $value)
		{
			if(!isset($this->original[$key]) || $this->original[$key] != $value)
			{
				$modified[$key] = $value;
			}
		}

		return $modified;
	}

	/**
	 * Generates a primary key.
	 * 
	 * @access  protected
	 * @return  string|int
	 */

	protected function generatePrimaryKey()
	{
		throw new RuntimeException(vsprintf("%s(): The '%s::generatePrimaryKey()' method must be implemented.", [__METHOD__, get_class($this)]));
	}

	/**
	 * Inserts a new record into the database.
	 * 
	 * @access  protected
	 */

	protected function insertRecord()
	{
		$this->exists = true;

		switch($this->primaryKeyType)
		{
			case static::PRIMARY_KEY_TYPE_UUID:
				$this->columns[$this->primaryKey] = UUID::v4();
				break;
			case static::PRIMARY_KEY_TYPE_CUSTOM:
				$this->columns[$this->primaryKey] = $this->generatePrimaryKey();
				break;
		}

		$this->hydrator()->insert($this->columns);

		if($this->primaryKeyType === static::PRIMARY_KEY_TYPE_INCREMENTING)
		{
			$connection = $this->getConnection();

			switch($connection->getDriver())
			{
				case 'pgsql':
					$sequence = $this->getTable() . '_' . $this->primaryKey . '_seq';
					break;
				default:
					$sequence = null;
			}

			$this->columns[$this->primaryKey] = $connection->getPDO()->lastInsertId($sequence);
		}
	}

	/**
	 * Updates an existing record.
	 * 
	 * @access  protected
	 * @return  boolean
	 */

	protected function updateRecord()
	{
		$query = $this->hydrator();

		$query->where($this->primaryKey, '=', $this->columns[$this->primaryKey]);

		if($this->enableLocking)
		{
			$lockVersion = $this->columns[$this->lockingColumn]++;

			$query->where($this->lockingColumn, '=', $lockVersion);
		}

		$result = $query->update($this->getModified());

		if($this->enableLocking && $result === 0)
		{
			$this->columns[$this->lockingColumn]--;

			throw new StaleRecordException(vsprintf("%s(): Attempted to update a stale record.", [__METHOD__]));
		}

		return (bool) $result;
	}

	/**
	 * Saves the record to the database.
	 * 
	 * @access  public
	 * @return  boolean
	 */

	public function save()
	{
		$success = true;

		if(!$this->exists)
		{
			// This is a new record so we need to insert it into the database.
				
			$this->insertRecord();
		}
		elseif($this->isModified())
		{
			// This record exists and is modified so all we have to do is update it.

			$success = $this->updateRecord();
		}

		if($success)
		{
			// Sync up if save was successful

			$this->original = $this->columns;
		}
		
		return $success;
	}

	/**
	 * Deletes a record from the database.
	 * 
	 * @access  public
	 * @return  boolean
	 */

	public function delete()
	{
		if($this->exists)
		{
			$query = $this->hydrator();

			if($this->enableLocking)
			{
				$query->where($this->lockingColumn, '=', $this->columns[$this->lockingColumn]);
			}

			$deleted = (bool) $query->where($this->primaryKey, '=', $this->columns[$this->primaryKey])->delete();

			if($deleted)
			{				
				$this->exists   = false;
				$this->original = [];
				$this->related  = [];
			}
			else
			{
				if($this->enableLocking)
				{
					throw new StaleRecordException(vsprintf("%s(): Attempted to delete a stale record.", [__METHOD__]));
				}
			}

			return $deleted;
		}

		return false;
	}

	/**
	 * Returns an array representation of the record.
	 * 
	 * @access  public
	 * @param   boolean  $protect  (optional) Protect columns?
	 * @param   boolean  $raw      (optional) Get raw values?
	 * @return  array
	 */

	public function toArray($protect = true, $raw = false)
	{
		if($raw)
		{
			$columns = $this->columns;
		}
		else
		{
			$columns = [];

			foreach($this->columns as $key => $value)
			{
				if(method_exists($this, 'get_' . $key))
				{
					$columns[$key] = $this->{'get_' . $key}($this->columns[$key]);
				}
				else
				{
					$columns[$key] = $this->columns[$key];
				}
			}
		}

		// Removes protected columns from the array

		if($protect === true && !empty($this->protected))
		{
			$columns = array_diff_key($columns, array_flip($this->protected));
		}

		// Merge in related records

		foreach($this->related as $relation => $related)
		{
			$columns = array_merge($columns, array($relation => $related->toArray($protect, $raw)));
		}

		// Returns array representation of the record
		
		return $columns;
	}

	/**
	 * Returns a json representation of the record.
	 * 
	 * @access  public
	 * @param   boolean  $protect  (optional) Protect columns?
	 * @param   boolean  $raw      (optional) Get raw values?
	 * @return  string
	 */

	public function toJson($protect = true, $raw = false)
	{
		return json_encode($this->toArray($protect, $raw));
	}

	/**
	 * Returns a json representation of the record.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function __toString()
	{
		return $this->toJson();
	}

	/**
	 * Forwards static method calls to the query builder.
	 * 
	 * @access  public
	 * @param   string  $name       Method name
	 * @param   array   $arguments  Method arguments
	 * @return  mixed
	 */

	public static function __callStatic($name, $arguments)
	{
		return call_user_func_array([(new static)->hydrator(), $name], $arguments);
	}
}


