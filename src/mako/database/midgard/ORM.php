<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\midgard;

use DateTimeInterface;
use JsonSerializable;
use RuntimeException;

use mako\application\Application;
use mako\chrono\Time;
use mako\database\ConnectionManager;
use mako\database\connections\Connection;
use mako\database\midgard\Query;
use mako\database\midgard\relations\BelongsTo;
use mako\database\midgard\relations\HasMany;
use mako\database\midgard\relations\HasManyPolymorphic;
use mako\database\midgard\relations\HasOne;
use mako\database\midgard\relations\HasOnePolymorphic;
use mako\database\midgard\relations\ManyToMany;
use mako\utility\Str;
use mako\utility\UUID;
use mako\syringe\ClassInspector;

/**
 * ORM.
 *
 * @author  Frederic G. Østby
 */
abstract class ORM implements JsonSerializable
{
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
	 * ORM query builder hooks.
	 *
	 * @var array
	 */
	protected static $hooks = [];

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
	 * @var bool
	 */
	protected $primaryKeyType = ORM::PRIMARY_KEY_TYPE_INCREMENTING;

	/**
	 * Has the record been loaded from/saved to a database?
	 *
	 * @var bool
	 */
	protected $exists = false;

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
	 * Columns that should be casted to a specific type.
	 *
	 * @var array
	 */
	protected $cast = [];

	/**
	 * Columns that can be set through mass assignment.
	 *
	 * @var array
	 */
	protected $assignable = [];

	/**
	 * Columns and relations that are excluded from the array and json representations of the record.
	 *
	 * @var array
	 */
	protected $protected = [];

	/**
	 * Date format used when returning array and json representations of the record.
	 *
	 * @var string
	 */
	protected $dateOutputFormat = 'Y-m-d H:i:s';

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   array   $columns    Column values
	 * @param   bool    $raw        Set raw values?
	 * @param   bool    $whitelist  Remove columns that are not in the whitelist?
	 * @param   bool    $exists     Does the record come from a database?
	 */
	public function __construct(array $columns = [], bool $raw = false, bool $whitelist = true, bool $exists = false)
	{
		$this->registerHooks();

		$this->assign($columns, $raw, $whitelist);

		if($exists)
		{
			$this->exists = true;

			$this->synchronize();
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
		}
	}

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
	 * @return  \mako\database\connections\Connection
	 */
	public function getConnection(): Connection
	{
		if(empty(static::$connectionManager))
		{
			static::$connectionManager = Application::instance()->getContainer()->get('database');
		}

		return static::$connectionManager->connection($this->connectionName);
	}

	/**
	 * Has the record been loaded from/saved to a database?
	 *
	 * @access  public
	 * @return  bool
	 */
	public function exists(): bool
	{
		return $this->exists;
	}

	/**
	 * Synchronizes the original values with the modified values.
	 *
	 * @access  public
	 */
	public function synchronize()
	{
		$this->original = $this->columns;
	}

	/**
	 * Gets the date format from the query builder compiler.
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function getDateFormat(): string
	{
		static $dateFormat;

		if($dateFormat === null)
		{
			$dateFormat = $this->builder()->getCompiler()->getDateFormat();
		}

		return $dateFormat;
	}

	/**
	 * Registers query builder hooks from traits.
	 *
	 * @access  protected
	 */
	protected function registerHooks()
	{
		if(!isset(static::$hooks[static::class]))
		{
			static::$hooks[static::class] = [];

			foreach(ClassInspector::getTraits(static::class) as $trait)
			{
				if(method_exists($this, $getter = 'get' . $this->getClassShortName($trait) . 'Hooks'))
				{
					static::$hooks[static::class] = array_merge_recursive(static::$hooks[static::class], $this->$getter());
				}
			}
		}
	}

	/**
	 * Binds the hooks to the current instance of "$this".
	 *
	 * @access  protected
	 * @param   array      $hooks  Array of hooks
	 * @return  array
	 */
	protected function bindHooks(array $hooks): array
	{
		$bound = [];

		foreach($hooks as $hook)
		{
			$bound[] = $hook->bindTo($this);
		}

		return $bound;
	}

	/**
	 * Returns hooks for the chosen event.
	 *
	 * @access  public
	 * @param   string  $event  Event name
	 * @return  array
	 */
	public function getHooks(string $event): array
	{
		return isset(static::$hooks[static::class][$event]) ? $this->bindHooks(static::$hooks[static::class][$event]) : [];
	}

	/**
	 * Returns the short name of a class.
	 *
	 * @access  protected
	 * @param   string     $className   Class name
	 * @return  string
	 */
	protected function getClassShortName(string $className = null): string
	{
		return basename(str_replace('\\', '/', $className ?? static::class));
	}

	/**
	 * Returns the table name of the model.
	 *
	 * @access  public
	 * @return  string
	 */
	public function getTable(): string
	{
		if(empty($this->tableName))
		{
			$this->tableName = Str::pluralize(Str::camel2underscored($this->getClassShortName()));
		}

		return $this->tableName;
	}

	/**
	 * Returns the primary key of the table.
	 *
	 * @access  public
	 * @return  string
	 */
	public function getPrimaryKey(): string
	{
		return $this->primaryKey;
	}

	/**
	 * Returns the primary key type.
	 *
	 * @access  public
	 * @return  int
	 */
	public function getPrimaryKeyType(): int
	{
		return $this->primaryKeyType;
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
	public function getForeignKey(): string
	{
		return strtolower($this->getClassShortName()) . '_id';
	}

	/**
	 * Returns the namespaced class name of the model.
	 *
	 * @access  public
	 * @return  string
	 */
	public function getClass(): string
	{
		return '\\' . static::class;
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
	public function getIncludes(): array
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
	public function setRelated(string $relation, $related)
	{
		$this->related[$relation] = $related;
	}

	/**
	 * Returns the related records array.
	 *
	 * @access  public
	 * @return  array
	 */
	public function getRelated(): array
	{
		return $this->related;
	}

	/**
	 * Returns the columns that we're casting.
	 *
	 * @access  protected
	 * @return  array
	 */
	protected function getCastColumns(): array
	{
		return $this->cast;
	}

	/**
	 * Cast value to the appropriate type.
	 *
	 * @access  protected
	 * @param   string     $name   Column name
	 * @param   mixed      $value  Column value
	 * @return  mixed
	 */
	protected function cast(string $name, $value)
	{
		$cast = $this->getCastColumns();

		if(isset($cast[$name]) && $value !== null)
		{
			switch($cast[$name])
			{
				case 'int':
					return (int) $value;
				case 'float':
					return (float) $value;
				case 'bool':
					return $value === 'f' ? false : (bool) $value;
				case 'date':
					return ($value instanceof DateTimeInterface) ? $value : Time::createFromFormat($this->getDateFormat(), $value);
				case 'string':
					return (string) $value;
				default:
					throw new RunTimeException(vsprintf("%s::%s(): Unsupported type [ %s ].", [static::class, __FUNCTION__, $cast[$name]]));
			}
		}

		return $value;
	}

	/**
	 * Sets a raw column value.
	 *
	 * @access  public
	 * @param   string  $name   Column name
	 * @param   mixed   $value  Column value
	 */
	public function setRawColumn(string $name, $value)
	{
		$this->columns[$name] = $this->cast($name, $value);
	}

	/**
	 * Sets a column value.
	 *
	 * @access  public
	 * @param   string   $name   Column name
	 * @param   mixed    $value  Column value
	 */
	public function setColumn(string $name, $value)
	{
		$value = $this->cast($name, $value);

		if(method_exists($this, $name . 'Mutator'))
		{
			$this->columns[$name] = $this->{$name . 'Mutator'}($value);
		}
		else
		{
			$this->columns[$name] = $value;
		}
	}

	/**
	 * Gets a raw column value.
	 *
	 * @access  public
	 * @param   string  $name  Column name
	 * @return  mixed
	 */
	public function getRawColumn(string $name)
	{
		if(array_key_exists($name, $this->columns))
		{
			return $this->columns[$name];
		}
		else
		{
			throw new RunTimeException(vsprintf("%s::%s(): Unknown column [ %s ].", [static::class, __FUNCTION__, $name]));
		}
	}

	/**
	 * Returns a column value.
	 *
	 * @access  protected
	 * @param   string  $name  Column name
	 * @return  mixed
	 */
	protected function getColumnValue(string $name)
	{
		if(method_exists($this, $name . 'Accessor'))
		{
			return $this->{$name . 'Accessor'}($this->columns[$name]);
		}

		return $this->columns[$name];
	}

	/**
	 * Returns TRUE if it's probable that $name is a relation and FALSE if not.
	 *
	 * @access  protected
	 * @param   string     $name  Relation name
	 * @return  bool
	 */
	protected function isRelation(string $name): bool
	{
		return method_exists(self::class, $name) === false && method_exists($this, $name);
	}

	/**
	 * Gets a column value.
	 *
	 * @access  public
	 * @param   string  $name  Column name
	 * @return  mixed
	 */
	public function getColumn(string $name)
	{
		if(array_key_exists($name, $this->columns))
		{
			// It's a database column

			return $this->getColumnValue($name);
		}
		elseif(array_key_exists($name, $this->related))
		{
			// The column is a cached or eagerly loaded relation

			return $this->related[$name];
		}
		elseif($this->isRelation($name))
		{
			// The column is a relation. Lazy load the records and cache them

			return $this->related[$name] = $this->{$name}()->getRelated();
		}
		else
		{
			throw new RunTimeException(vsprintf("%s(): Unknown column [ %s ].", [__METHOD__, $name]));
		}
	}

	/**
	 * Returns the columns array.
	 *
	 * @access  public
	 * @return  array
	 */
	public function getRawColumns(): array
	{
		return $this->columns;
	}

	/**
	 * Assigns the column values to the model.
	 *
	 * @access  public
	 * @param   array                       $columns    Column values
	 * @param   bool                        $raw        Set raw values?
	 * @param   bool                        $whitelist  Remove columns that are not in the whitelist?
	 * @return  \mako\database\midgard\ORM
	 */
	public function assign(array $columns, bool $raw = false, bool $whitelist = true): ORM
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

		if($raw)
		{
			foreach($columns as $column => $value)
			{
				$this->setRawColumn($column, $value);
			}
		}
		else
		{
			foreach($columns as $column => $value)
			{
				$this->setColumn($column, $value);
			}
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
	public function __set(string $name, $value)
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
	public function __get(string $name)
	{
		return $this->getColumn($name);
	}

	/**
	 * Checks if a column or relation is set using overloading.
	 *
	 * @access  public
	 * @param   string  $name  Column name
	 * @return  bool
	 */
	public function __isset(string $name)
	{
		return isset($this->columns[$name]) || isset($this->related[$name]) || ($this->isRelation($name) && $this->$name && isset($this->related[$name]));
	}

	/**
	 * Unset column value or relation using overloading.
	 *
	 * @access  public
	 * @param   string  $name  Column name
	 */
	public function __unset(string $name)
	{
		unset($this->columns[$name], $this->related[$name]);
	}

	/**
	 * Returns a query builder instance.
	 *
	 * @access  public
	 * @return  \mako\database\midgard\Query
	 */
	public function builder(): Query
	{
		return new Query($this->getConnection(), $this);
	}

	/**
	 * Returns a record using the value of its primary key.
	 *
	 * @access  public
	 * @param   mixed                            $id       Primary key
	 * @param   array                            $columns  Columns to select
	 * @return  \mako\database\midgard\ORM|bool
	 */
	public static function get($id, array $columns = [])
	{
		return (new static)->builder()->get($id, $columns);
	}

	/**
	 * Creates a new record and returns the model.
	 *
	 * @access  public
	 * @param   array                       $columns    Column values
	 * @param   bool                        $raw        Set raw values?
	 * @param   bool                        $whitelist  Remove columns that are not in the whitelist?
	 * @return  \mako\database\midgard\ORM
	 */
	public static function create(array $columns, bool $raw = false, bool $whitelist = true): ORM
	{
		$model = new static($columns, $raw, $whitelist);

		$model->save();

		return $model;
	}

	/**
	 * Returns a HasOne relation.
	 *
	 * @access  protected
	 * @param   string                                   $model       Related model
	 * @param   string|null                              $foreignKey  Foreign key name
	 * @return  \mako\database\midgard\relations\HasOne
	 */
	protected function hasOne(string $model, string $foreignKey = null): HasOne
	{
		$related = new $model;

		return new HasOne($related->getConnection(), $this, $related, $foreignKey);
	}

	/**
	 * Returns a HasOnePolymorphic relation.
	 *
	 * @access  protected
	 * @param   string                                               $model            Related model
	 * @param   string                                               $polymorphicType  Polymorphic type
	 * @return  \mako\database\midgard\relations\HasManyPolymorphic
	 */
	protected function hasOnePolymorphic(string $model, string $polymorphicType): HasOnePolymorphic
	{
		$related = new $model;

		return new HasOnePolymorphic($related->getConnection(), $this, $related, $polymorphicType);
	}

	/**
	 * Returns a HasMany relation.
	 *
	 * @access  protected
	 * @param   string                                    $model       Related model
	 * @param   string|null                               $foreignKey  Foreign key name
	 * @return  \mako\database\midgard\relations\HasMany
	 */
	protected function hasMany(string $model, string $foreignKey = null): HasMany
	{
		$related = new $model;

		return new HasMany($related->getConnection(), $this, $related, $foreignKey);
	}

	/**
	 * Returns a HasManyPolymorphic relation.
	 *
	 * @access  protected
	 * @param   string                                               $model            Related model
	 * @param   string                                               $polymorphicType  Polymorphic type
	 * @return  \mako\database\midgard\relations\HasManyPolymorphic
	 */
	protected function hasManyPolymorphic(string $model, string $polymorphicType): HasManyPolymorphic
	{
		$related = new $model;

		return new HasManyPolymorphic($related->getConnection(), $this, $related, $polymorphicType);
	}

	/**
	 * Returns a ManyToMany relation.
	 *
	 * @access  protected
	 * @param   string                                       $model          Related model
	 * @param   string|null                                  $foreignKey     Foreign key name
	 * @param   string|null                                  $junctionTable  Junction table name
	 * @param   string|null                                  $junctionKey    Junction key name
	 * @return  \mako\database\midgard\relations\ManyToMany
	 */
	protected function manyToMany(string $model, string $foreignKey = null, string $junctionTable = null, string $junctionKey = null): ManyToMany
	{
		$related = new $model;

		return new ManyToMany($related->getConnection(), $this, $related, $foreignKey, $junctionTable, $junctionKey);
	}

	/**
	 * Returns a BelongsTo relation.
	 *
	 * @access  protected
	 * @param   string                                      $model       Related model
	 * @param   string|null                                 $foreignKey  Foreign key name
	 * @return  \mako\database\midgard\relations\BelongsTo
	 */
	protected function belongsTo(string $model, string $foreignKey = null): BelongsTo
	{
		$related = new $model;

		return new BelongsTo($related->getConnection(), $this, $related, $foreignKey);
	}

	/**
	 * Has the record been modified?
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isModified(): bool
	{
		return count($this->getModified()) > 0;
	}

	/**
	 * Returns the modified column values of the record.
	 *
	 * @access  public
	 * @return  array
	 */
	public function getModified(): array
	{
		$modified = [];

		foreach($this->columns as $key => $value)
		{
			if(!isset($this->original[$key]) || $this->original[$key] !== $value)
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
	 * @return  mixed
	 */
	protected function generatePrimaryKey()
	{
		throw new RuntimeException(vsprintf("%s(): The '%s::generatePrimaryKey()' method must be implemented.", [__METHOD__, static::class]));
	}

	/**
	 * Inserts a new record into the database.
	 *
	 * @access  protected
	 * @param   \mako\database\midgard\Query  $query  Query builder
	 */
	protected function insertRecord(Query $query)
	{
		if($this->primaryKeyType === static::PRIMARY_KEY_TYPE_INCREMENTING)
		{
			$this->columns[$this->primaryKey] = $query->insertAndGetId($this->columns, $this->primaryKey);
		}
		else
		{
			switch($this->primaryKeyType)
			{
				case static::PRIMARY_KEY_TYPE_UUID:
					$this->columns[$this->primaryKey] = UUID::v4();
					break;
				case static::PRIMARY_KEY_TYPE_CUSTOM:
					$this->columns[$this->primaryKey] = $this->generatePrimaryKey();
					break;
			}

			$query->insert($this->columns);
		}
	}

	/**
	 * Updates an existing record.
	 *
	 * @access  protected
	 * @param   \mako\database\midgard\Query  $query  Query builder
	 * @return  bool
	 */
	protected function updateRecord(Query $query): bool
	{
		$query->where($this->primaryKey, '=', $this->columns[$this->primaryKey]);

		return (bool) $query->update($this->getModified());
	}

	/**
	 * Saves the record to the database.
	 *
	 * @access  public
	 * @return  bool
	 */
	public function save(): bool
	{
		$success = true;

		if(!$this->exists)
		{
			// This is a new record so we need to insert it into the database.

			$this->insertRecord($this->builder());

			$this->exists = true;
		}
		elseif($this->isModified())
		{
			// This record exists and is modified so all we have to do is update it.

			$success = $this->updateRecord($this->builder());
		}

		if($success)
		{
			// Sync up if save was successful

			$this->synchronize();
		}

		return $success;
	}

	/**
	 * Deletes a record from the database.
	 *
	 * @access  protected
	 * @param   \mako\database\midgard\Query  $query  Query builder
	 * @return  bool
	 */
	protected function deleteRecord(Query $query): bool
	{
		return (bool) $query->where($this->primaryKey, '=', $this->columns[$this->primaryKey])->delete();
	}

	/**
	 * Deletes a record from the database.
	 *
	 * @access  public
	 * @return  bool
	 */
	public function delete(): bool
	{
		if($this->exists)
		{
			$deleted = $this->deleteRecord($this->builder());

			if($deleted)
			{
				$this->exists   = false;
				$this->original = [];
				$this->related  = [];
			}

			return $deleted;
		}

		return false;
	}

	/**
	 * Excludes the chosen columns and relations from array and json representations of the record.
	 * You expose all fields by passing FALSE.
	 *
	 * @access  public
	 * @param   string|array|bool           $column  Column or relation to hide from the
	 * @return  \mako\database\midgard\ORM
	 */
	public function protect($column): ORM
	{
		if($column === false)
		{
			$this->protected = [];
		}
		else
		{
			$this->protected = array_unique(array_merge($this->protected, (array) $column));
		}

		return $this;
	}

	/**
	 * Exposes the chosen columns and relations in the array and json representations of the record.
	 * You can expose all fields by passing TRUE.
	 *
	 * @access  public
	 * @param   string|array|bool           $column  Column or relation to hide from the
	 * @return  \mako\database\midgard\ORM
	 */
	public function expose($column): ORM
	{
		if($column === true)
		{
			$this->protected = [];
		}
		else
		{
			$this->protected = array_diff($this->protected, (array) $column);
		}

		return $this;
	}

	/**
	 * Returns an array representation of the record.
	 *
	 * @access  public
	 * @return  array
	 */
	public function toArray(): array
	{
		$columns = $this->columns;

		// Removes protected columns from the array

		if(!empty($this->protected))
		{
			$columns = array_diff_key($columns, array_flip($this->protected));
		}

		// Mutate column values if needed

		foreach($columns as $name => $value)
		{
			$value = $this->getColumnValue($name);

			if($value instanceof DateTimeInterface)
			{
				$value = $value->format($this->dateOutputFormat);
			}

			$columns[$name] = $value;
		}

		// Merge in related records

		foreach($this->related as $relation => $related)
		{
			if(in_array($relation, $this->protected))
			{
				continue;
			}

			$columns += [$relation => $related->toArray()];
		}

		// Returns array representation of the record

		return $columns;
	}

	/**
	 * Returns data which can be serialized by json_encode().
	 *
	 * @access  public
	 * @return  array
	 */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}

	/**
	 * Returns a json representation of the record.
	 *
	 * @access  public
	 * @param   int     $options  JSON encode options
	 * @return  string
	 */
	public function toJson(int $options = 0): string
	{
		return json_encode($this->toArray(), $options);
	}

	/**
	 * Returns a json representation of the record.
	 *
	 * @access  public
	 * @return  string
	 */
	public function __toString(): string
	{
		return $this->toJson();
	}

	/**
	 * Forwards method calls to the query builder.
	 *
	 * @access  public
	 * @param   string  $name       Method name
	 * @param   array   $arguments  Method arguments
	 * @return  mixed
	 */
	public function __call(string $name, array $arguments)
	{
		return $this->builder()->{$name}(...$arguments);
	}

	/**
	 * Forwards static method calls to the query builder.
	 *
	 * @access  public
	 * @param   string  $name       Method name
	 * @param   array   $arguments  Method arguments
	 * @return  mixed
	 */
	public static function __callStatic(string $name, array $arguments)
	{
		return (new static)->builder()->{$name}(...$arguments);
	}
}