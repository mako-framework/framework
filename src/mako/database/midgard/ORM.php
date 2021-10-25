<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard;

use DateTimeInterface;
use JsonSerializable;
use mako\application\Application;
use mako\chrono\Time;
use mako\database\ConnectionManager;
use mako\database\connections\Connection;
use mako\database\midgard\relations\BelongsTo;
use mako\database\midgard\relations\BelongsToPolymorphic;
use mako\database\midgard\relations\HasMany;
use mako\database\midgard\relations\HasManyPolymorphic;
use mako\database\midgard\relations\HasOne;
use mako\database\midgard\relations\HasOnePolymorphic;
use mako\database\midgard\relations\ManyToMany;
use mako\syringe\ClassInspector;
use mako\utility\Str;
use mako\utility\UUID;
use RuntimeException;

use function array_diff;
use function array_diff_key;
use function array_flip;
use function array_intersect_key;
use function array_key_exists;
use function array_merge_recursive;
use function array_unique;
use function count;
use function in_array;
use function json_encode;
use function method_exists;
use function strrpos;
use function substr;
use function vsprintf;

/**
 * ORM.
 *
 * @author Frederic G. Østby
 */
abstract class ORM implements JsonSerializable
{
	/**
	 * Incrementing primary key.
	 *
	 * @var int
	 */
	public const PRIMARY_KEY_TYPE_INCREMENTING = 1000;

	/**
	 * UUID primary key.
	 *
	 * @var int
	 */
	public const PRIMARY_KEY_TYPE_UUID = 1001;

	/**
	 * Custom primary key.
	 *
	 * @var int
	 */
	public const PRIMARY_KEY_TYPE_CUSTOM = 1002;

	/**
	 * No primary key.
	 *
	 * @var int
	 */
	public const PRIMARY_KEY_TYPE_NONE = 1003;

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
	protected static $traitHooks = [];

	/**
	 * Trait casts.
	 *
	 * @var array
	 */
	protected static $traitCasts = [];

	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected $tableName = null;

	/**
	 * Foreign key name.
	 *
	 * @var string
	 */
	protected $foreignKeyName = null;

	/**
	 * Primary key.
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * Does this table have an auto increment primary index?
	 *
	 * @var int
	 */
	protected $primaryKeyType = ORM::PRIMARY_KEY_TYPE_INCREMENTING;

	/**
	 * Has the record been loaded from/saved to a database?
	 *
	 * @var bool
	 */
	protected $isPersisted = false;

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
	 * @param array $columns     Column values
	 * @param bool  $raw         Set raw values?
	 * @param bool  $whitelist   Remove columns that are not in the whitelist?
	 * @param bool  $isPersisted Does the record come from a database?
	 */
	public function __construct(array $columns = [], bool $raw = false, bool $whitelist = true, bool $isPersisted = false)
	{
		$this->registerHooksAndCasts();

		$this->assign($columns, $raw, $whitelist);

		if($isPersisted)
		{
			$this->isPersisted = true;

			$this->synchronize();
		}
	}

	/**
	 * Making sure that cloning returns a "fresh copy" of the record.
	 */
	public function __clone()
	{
		if($this->isPersisted)
		{
			$this->isPersisted = false;
			$this->original    = [];
			$this->related     = [];

			unset($this->columns[$this->primaryKey]);
		}
	}

	/**
	 * Set the connection manager.
	 *
	 * @param \mako\database\ConnectionManager $connectionManager Connection manager instance
	 */
	public static function setConnectionManager(ConnectionManager $connectionManager): void
	{
		static::$connectionManager = $connectionManager;
	}

	/**
	 * Returns the connection of the model.
	 *
	 * @return \mako\database\connections\Connection
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
	 * @return bool
	 */
	public function isPersisted(): bool
	{
		return $this->isPersisted;
	}

	/**
	 * Synchronizes the original values with the modified values.
	 */
	public function synchronize(): void
	{
		$this->original = $this->columns;
	}

	/**
	 * Gets the date format from the query builder compiler.
	 *
	 * @return string
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
	 * Registers hooks and casts.
	 */
	protected function registerHooksAndCasts(): void
	{
		if(!isset(static::$traitHooks[static::class]))
		{
			static::$traitHooks[static::class] = [];
			static::$traitCasts[static::class] = [];

			foreach(ClassInspector::getTraits(static::class) as $trait)
			{
				if(method_exists($this, $getter = "get{$this->getClassShortName($trait)}Hooks"))
				{
					static::$traitHooks[static::class] = array_merge_recursive(static::$traitHooks[static::class], $this->$getter());
				}

				if(method_exists($this, $getter = "get{$this->getClassShortName($trait)}Casts"))
				{
					static::$traitCasts[static::class] += $this->$getter();
				}
			}
		}

		$this->cast += static::$traitCasts[static::class];
	}

	/**
	 * Binds the hooks to the current instance of "$this".
	 *
	 * @param  array $hooks Array of hooks
	 * @return array
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
	 * @param  string $event Event name
	 * @return array
	 */
	public function getHooks(string $event): array
	{
		return isset(static::$traitHooks[static::class][$event]) ? $this->bindHooks(static::$traitHooks[static::class][$event]) : [];
	}

	/**
	 * Returns the short name of a class.
	 *
	 * @param  string|null $className Class name
	 * @return string
	 */
	protected function getClassShortName(?string $className = null): string
	{
		$class = $className ?? static::class;

		$pos = strrpos($class, '\\');

		if($pos === false)
		{
			return $class;
		}

		return substr($class, $pos + 1);
	}

	/**
	 * Returns the table name of the model.
	 *
	 * @return string
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
	 * @return string
	 */
	public function getPrimaryKey(): string
	{
		return $this->primaryKey;
	}

	/**
	 * Returns the primary key type.
	 *
	 * @return int
	 */
	public function getPrimaryKeyType(): int
	{
		return $this->primaryKeyType;
	}

	/**
	 * Returns the primary key value.
	 *
	 * @return mixed
	 */
	public function getPrimaryKeyValue()
	{
		return $this->columns[$this->primaryKey];
	}

	/**
	 * Returns the foreign key of the table.
	 *
	 * @return string
	 */
	public function getForeignKey(): string
	{
		if(empty($this->foreignKeyName))
		{
			$this->foreignKeyName = Str::camel2underscored($this->getClassShortName()) . '_id';
		}

		return $this->foreignKeyName;
	}

	/**
	 * Returns the namespaced class name of the model.
	 *
	 * @return string
	 */
	public function getClass(): string
	{
		return '\\' . static::class;
	}

	/**
	 * Sets the relations to eager load.
	 *
	 * @param array $includes Relations to eager load
	 */
	public function setIncludes(array $includes): void
	{
		$this->including = $includes;
	}

	/**
	 * Returns the relations to eager load.
	 *
	 * @return array
	 */
	public function getIncludes(): array
	{
		return $this->including;
	}

	/**
	 * Sets eagerly loaded related records.
	 *
	 * @param string $relation Relation name
	 * @param mixed  $related  Related record(s)
	 */
	public function setRelated(string $relation, $related): void
	{
		$this->related[$relation] = $related;
	}

	/**
	 * Returns TRUE if the model has included the relationship and FALSE if not.
	 *
	 * @param  string $relation Relation name
	 * @return bool
	 */
	public function includes(string $relation): bool
	{
		return array_key_exists($relation, $this->related);
	}

	/**
	 * Eager loads relations on the model.
	 *
	 * @param  array|string $includes Relation or array of relations to eager load
	 * @return $this
	 */
	public function include($includes)
	{
		(function($includes, $model): void
		{
			$this->including($includes)->loadIncludes([$model]);
		})->bindTo($this->builder(), Query::class)($includes, $this);

		return $this;
	}

	/**
	 * Returns the related records array.
	 *
	 * @return array
	 */
	public function getRelated(): array
	{
		return $this->related;
	}

	/**
	 * Cast value to the appropriate type.
	 *
	 * @param  string $name  Column name
	 * @param  mixed  $value Column value
	 * @return mixed
	 */
	protected function cast(string $name, $value)
	{
		if(isset($this->cast[$name]) && $value !== null)
		{
			switch($this->cast[$name])
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
					throw new RuntimeException(vsprintf('Unsupported type [ %s ].', [$this->cast[$name]]));
			}
		}

		return $value;
	}

	/**
	 * Sets a raw column value.
	 *
	 * @param string $name  Column name
	 * @param mixed  $value Column value
	 */
	public function setRawColumnValue(string $name, $value): void
	{
		$this->columns[$name] = $this->cast($name, $value);
	}

	/**
	 * Sets a column value.
	 *
	 * @param string $name  Column name
	 * @param mixed  $value Column value
	 */
	public function setColumnValue(string $name, $value): void
	{
		$value = $this->cast($name, $value);

		if(method_exists($this, "{$name}Mutator"))
		{
			$this->columns[$name] = $this->{"{$name}Mutator"}($value);
		}
		else
		{
			$this->columns[$name] = $value;
		}
	}

	/**
	 * Gets a raw column value.
	 *
	 * @param  string $name Column name
	 * @return mixed
	 */
	public function getRawColumnValue(string $name)
	{
		return $this->columns[$name];
	}

	/**
	 * Returns a column value.
	 *
	 * @param  string $name Column name
	 * @return mixed
	 */
	public function getColumnValue(string $name)
	{
		if(method_exists($this, "{$name}Accessor"))
		{
			return $this->{"{$name}Accessor"}($this->columns[$name]);
		}

		return $this->columns[$name];
	}

	/**
	 * Returns TRUE if it's probable that $name is a relation and FALSE if not.
	 *
	 * @param  string $name Relation name
	 * @return bool
	 */
	protected function isRelation(string $name): bool
	{
		return method_exists(self::class, $name) === false && method_exists($this, $name);
	}

	/**
	 * Gets a column value or relation.
	 *
	 * @param  string $name Column name
	 * @return mixed
	 */
	public function getValue(string $name)
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
			// The column is a relation. Lazy load the record(s) and cache them

			return $this->related[$name] = $this->$name()->getRelated();
		}

		// All options have been exhausted so we'll throw an exception

		throw new RuntimeException(vsprintf('Unknown column or relation [ %s ].', [$name]));
	}

	/**
	 * Returns the columns array.
	 *
	 * @return array
	 */
	public function getRawColumnValues(): array
	{
		return $this->columns;
	}

	/**
	 * Sets column values.
	 *
	 * @param array $columns Column values
	 * @param bool  $raw     Set raw values?
	 */
	protected function setColumValues(array $columns, bool $raw): void
	{
		if($raw)
		{
			if(empty($this->cast))
			{
				$this->columns = $columns;
			}
			else
			{
				foreach($columns as $column => $value)
				{
					$this->setRawColumnValue($column, $value);
				}
			}
		}
		else
		{
			foreach($columns as $column => $value)
			{
				$this->setColumnValue($column, $value);
			}
		}
	}

	/**
	 * Assigns the column values to the model.
	 *
	 * @param  array $columns   Column values
	 * @param  bool  $raw       Set raw values?
	 * @param  bool  $whitelist Remove columns that are not in the whitelist?
	 * @return $this
	 */
	public function assign(array $columns, bool $raw = false, bool $whitelist = true)
	{
		// Remove columns that are not in the whitelist

		if($whitelist && !empty($this->assignable))
		{
			$columns = array_intersect_key($columns, array_flip($this->assignable));
		}

		// Remove the primary key if the model has already beed loaded

		if($this->isPersisted && isset($columns[$this->primaryKey]))
		{
			unset($columns[$this->primaryKey]);
		}

		// Set column values

		$this->setColumValues($columns, $raw);

		return $this;
	}

	/**
	 * Set column value using overloading.
	 *
	 * @param string $name  Column name
	 * @param mixed  $value Column value
	 */
	public function __set(string $name, $value): void
	{
		$this->setColumnValue($name, $value);
	}

	/**
	 * Get column value or relation using overloading.
	 *
	 * @param  string $name Column name
	 * @return mixed
	 */
	public function __get(string $name)
	{
		return $this->getValue($name);
	}

	/**
	 * Checks if a column or relation is set using overloading.
	 *
	 * @param  string $name Column or relation name
	 * @return bool
	 */
	public function __isset(string $name)
	{
		if(isset($this->columns[$name]) || isset($this->related[$name]))
		{
			return true;
		}

		return $this->isRelation($name) && $this->getValue($name) !== null;
	}

	/**
	 * Unset column value or relation using overloading.
	 *
	 * @param string $name Column name
	 */
	public function __unset(string $name): void
	{
		unset($this->columns[$name], $this->related[$name]);
	}

	/**
	 * Returns a query builder instance.
	 *
	 * @return \mako\database\midgard\Query
	 */
	public function builder(): Query
	{
		return new Query($this->getConnection(), $this);
	}

	/**
	 * Returns a record using the value of its primary key.
	 *
	 * @param  mixed       $id      Primary key
	 * @param  array       $columns Columns to select
	 * @return static|null
	 */
	public static function get($id, array $columns = [])
	{
		return (new static)->builder()->get($id, $columns);
	}

	/**
	 * Creates a new record and returns the model.
	 *
	 * @param  array  $columns   Column values
	 * @param  bool   $raw       Set raw values?
	 * @param  bool   $whitelist Remove columns that are not in the whitelist?
	 * @return static
	 */
	public static function create(array $columns = [], bool $raw = false, bool $whitelist = true)
	{
		$model = new static($columns, $raw, $whitelist);

		$model->save();

		return $model;
	}

	/**
	 * Returns a HasOne relation.
	 *
	 * @param  string                                  $model      Related model
	 * @param  string|null                             $foreignKey Foreign key name
	 * @return \mako\database\midgard\relations\HasOne
	 */
	protected function hasOne(string $model, ?string $foreignKey = null): HasOne
	{
		$related = new $model;

		return new HasOne($related->getConnection(), $this, $related, $foreignKey);
	}

	/**
	 * Returns a HasOnePolymorphic relation.
	 *
	 * @param  string                                             $model           Related model
	 * @param  string                                             $polymorphicType Polymorphic type
	 * @return \mako\database\midgard\relations\HasOnePolymorphic
	 */
	protected function hasOnePolymorphic(string $model, string $polymorphicType): HasOnePolymorphic
	{
		$related = new $model;

		return new HasOnePolymorphic($related->getConnection(), $this, $related, $polymorphicType);
	}

	/**
	 * Returns a HasMany relation.
	 *
	 * @param  string                                   $model      Related model
	 * @param  string|null                              $foreignKey Foreign key name
	 * @return \mako\database\midgard\relations\HasMany
	 */
	protected function hasMany(string $model, ?string $foreignKey = null): HasMany
	{
		$related = new $model;

		return new HasMany($related->getConnection(), $this, $related, $foreignKey);
	}

	/**
	 * Returns a HasManyPolymorphic relation.
	 *
	 * @param  string                                              $model           Related model
	 * @param  string                                              $polymorphicType Polymorphic type
	 * @return \mako\database\midgard\relations\HasManyPolymorphic
	 */
	protected function hasManyPolymorphic(string $model, string $polymorphicType): HasManyPolymorphic
	{
		$related = new $model;

		return new HasManyPolymorphic($related->getConnection(), $this, $related, $polymorphicType);
	}

	/**
	 * Returns a ManyToMany relation.
	 *
	 * @param  string                                      $model         Related model
	 * @param  string|null                                 $foreignKey    Foreign key name
	 * @param  string|null                                 $junctionTable Junction table name
	 * @param  string|null                                 $junctionKey   Junction key name
	 * @return \mako\database\midgard\relations\ManyToMany
	 */
	protected function manyToMany(string $model, ?string $foreignKey = null, ?string $junctionTable = null, ?string $junctionKey = null): ManyToMany
	{
		$related = new $model;

		return new ManyToMany($related->getConnection(), $this, $related, $foreignKey, $junctionTable, $junctionKey);
	}

	/**
	 * Returns a BelongsTo relation.
	 *
	 * @param  string                                     $model      Related model
	 * @param  string|null                                $foreignKey Foreign key name
	 * @return \mako\database\midgard\relations\BelongsTo
	 */
	protected function belongsTo(string $model, ?string $foreignKey = null): BelongsTo
	{
		$related = new $model;

		return new BelongsTo($related->getConnection(), $this, $related, $foreignKey);
	}

	/**
	 * Returns a BelongsToPolymorphic relation.
	 *
	 * @param  string                                                $model           Related model
	 * @param  string                                                $polymorphicType Polymorphic type
	 * @return \mako\database\midgard\relations\BelongsToPolymorphic
	 */
	protected function belongsToPolymorphic(string $model, string $polymorphicType): BelongsToPolymorphic
	{
		$related = new $model;

		return new BelongsToPolymorphic($related->getConnection(), $this, $related, $polymorphicType);
	}

	/**
	 * Has the record been modified?
	 *
	 * @return bool
	 */
	public function isModified(): bool
	{
		return count($this->getModified()) > 0;
	}

	/**
	 * Returns the modified column values of the record.
	 *
	 * @return array
	 */
	public function getModified(): array
	{
		$modified = [];

		foreach($this->columns as $key => $value)
		{
			if(!array_key_exists($key, $this->original) || $this->original[$key] !== $value)
			{
				$modified[$key] = $value;
			}
		}

		return $modified;
	}

	/**
	 * Generates a primary key.
	 *
	 * @return mixed
	 */
	protected function generatePrimaryKey()
	{
		throw new RuntimeException(vsprintf('The [ %s::generatePrimaryKey() ] method must be implemented.', [static::class]));
	}

	/**
	 * Inserts a new record into the database.
	 *
	 * @param \mako\database\midgard\Query $query Query builder
	 */
	protected function insertRecord(Query $query): void
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
	 * @param  \mako\database\midgard\Query $query Query builder
	 * @return bool
	 */
	protected function updateRecord(Query $query): bool
	{
		$query->where($this->primaryKey, '=', $this->columns[$this->primaryKey]);

		return (bool) $query->update($this->getModified());
	}

	/**
	 * Saves the record to the database.
	 *
	 * @return bool
	 */
	public function save(): bool
	{
		$success = true;

		if(!$this->isPersisted)
		{
			// This is a new record so we need to insert it into the database.

			$this->insertRecord($this->builder());

			$this->isPersisted = true;
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
	 * @param  \mako\database\midgard\Query $query Query builder
	 * @return bool
	 */
	protected function deleteRecord(Query $query): bool
	{
		return (bool) $query->where($this->primaryKey, '=', $this->columns[$this->primaryKey])->delete();
	}

	/**
	 * Deletes a record from the database.
	 *
	 * @return bool
	 */
	public function delete(): bool
	{
		if($this->isPersisted)
		{
			$deleted = $this->deleteRecord($this->builder());

			if($deleted)
			{
				$this->isPersisted = false;
				$this->original    = [];
				$this->related     = [];
			}

			return $deleted;
		}

		return false;
	}

	/**
	 * Excludes the chosen columns and relations from array and json representations of the record.
	 * You expose all fields by passing FALSE.
	 *
	 * @param  string|array|false $column Column or relation to hide from the
	 * @return $this
	 */
	public function protect($column)
	{
		$this->protected = $column === false ? [] : array_unique(array_merge($this->protected, (array) $column));

		return $this;
	}

	/**
	 * Exposes the chosen columns and relations in the array and json representations of the record.
	 * You can expose all fields by passing TRUE.
	 *
	 * @param  string|array|true $column Column or relation to hide from the
	 * @return $this
	 */
	public function expose($column)
	{
		$this->protected = $column === true ? [] : array_diff($this->protected, (array) $column);

		return $this;
	}

	/**
	 * Returns an array representation of the record.
	 *
	 * @return array
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

			$columns += [$relation => ($related === null ? $related : $related->toArray())];
		}

		// Returns array representation of the record

		return $columns;
	}

	/**
	 * Returns data which can be serialized by json_encode().
	 *
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}

	/**
	 * Returns a json representation of the record.
	 *
	 * @param  int    $options JSON encode options
	 * @return string
	 */
	public function toJson(int $options = 0): string
	{
		return json_encode($this->toArray(), $options);
	}

	/**
	 * Returns a json representation of the record.
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->toJson();
	}

	/**
	 * Forwards method calls to the query builder.
	 *
	 * @param  string $name      Method name
	 * @param  array  $arguments Method arguments
	 * @return mixed
	 */
	public function __call(string $name, array $arguments)
	{
		return $this->builder()->$name(...$arguments);
	}

	/**
	 * Forwards static method calls to the query builder.
	 *
	 * @param  string $name      Method name
	 * @param  array  $arguments Method arguments
	 * @return mixed
	 */
	public static function __callStatic(string $name, array $arguments)
	{
		return (new static)->builder()->$name(...$arguments);
	}
}
