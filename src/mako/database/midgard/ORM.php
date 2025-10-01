<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard;

use DateTimeInterface;
use JsonSerializable;
use mako\application\CurrentApplication;
use mako\chrono\Time;
use mako\classes\ClassInspector;
use mako\database\ConnectionManager;
use mako\database\connections\Connection;
use mako\database\exceptions\DatabaseException;
use mako\database\exceptions\NotFoundException;
use mako\database\midgard\relations\BelongsTo;
use mako\database\midgard\relations\BelongsToPolymorphic;
use mako\database\midgard\relations\HasMany;
use mako\database\midgard\relations\HasManyPolymorphic;
use mako\database\midgard\relations\HasOne;
use mako\database\midgard\relations\HasOnePolymorphic;
use mako\database\midgard\relations\ManyToMany;
use mako\utility\Str;
use mako\utility\UUID;
use Override;
use Stringable;

use function array_diff;
use function array_diff_key;
use function array_filter;
use function array_flip;
use function array_intersect_key;
use function array_key_exists;
use function array_key_first;
use function array_map;
use function array_merge_recursive;
use function array_unique;
use function count;
use function in_array;
use function is_array;
use function is_object;
use function json_encode;
use function method_exists;
use function sprintf;
use function str_starts_with;
use function strlen;
use function strrpos;
use function substr;

/**
 * ORM.
 *
 * @mixin \mako\database\midgard\Query
 */
abstract class ORM implements JsonSerializable, Stringable
{
	/**
	 * Incrementing primary key.
	 */
	protected const int PRIMARY_KEY_TYPE_INCREMENTING = 1000;

	/**
	 * UUID primary key.
	 */
	protected const int PRIMARY_KEY_TYPE_UUID = 1001;

	/**
	 * Custom primary key.
	 */
	protected const int PRIMARY_KEY_TYPE_CUSTOM = 1002;

	/**
	 * No primary key.
	 */
	protected const int PRIMARY_KEY_TYPE_NONE = 1003;

	/**
	 * Connection name to use for the model.
	 */
	protected ?string $connectionName = null;

	/**
	 * Connection manager instance.
	 */
	protected static ?ConnectionManager $connectionManager = null;

	/**
	 * ORM query builder hooks.
	 */
	protected static array $traitHooks = [];

	/**
	 * Trait casts.
	 */
	protected static array $traitCasts = [];

	/**
	 * Table name.
	 */
	protected string $tableName;

	/**
	 * Foreign key name.
	 */
	protected string $foreignKeyName;

	/**
	 * Primary key.
	 */
	protected string $primaryKey = 'id';

	/**
	 * The primary key type of the table.
	 */
	protected int $primaryKeyType = ORM::PRIMARY_KEY_TYPE_INCREMENTING;

	/**
	 * Has the record been loaded from/saved to a database?
	 */
	protected bool $isPersisted = false;

	/**
	 * Column values.
	 */
	protected array $columns = [];

	/**
	 * Original column values.
	 */
	protected array $original = [];

	/**
	 * Relations to eager load.
	 */
	protected array $including = [];

	/**
	 * Related records.
	 */
	protected array $related = [];

	/**
	 * Columns that should be casted to a specific type.
	 */
	protected array $cast = [];

	/**
	 * Columns that can be set through mass assignment.
	 */
	protected array $assignable = [];

	/**
	 * Columns and relations that are excluded from the array and json representations of the record.
	 */
	protected array $protected = [];

	/**
	 * Date format used when returning array and json representations of the record.
	 */
	protected string $dateOutputFormat = 'Y-m-d\TH:i:sP'; // ISO-8601 (2025-01-01T00:00+00:00)

	/**
	 * Constructor.
	 */
	final public function __construct(array $columns = [], bool $raw = false, bool $whitelist = true, bool $isPersisted = false)
	{
		$this->registerHooksAndCasts();

		$this->assign($columns, $raw, $whitelist);

		if ($isPersisted) {
			$this->isPersisted = true;

			$this->synchronize();
		}
	}

	/**
	 * Making sure that cloning returns a "fresh copy" of the record.
	 */
	public function __clone()
	{
		if ($this->isPersisted) {
			$this->isPersisted = false;
			$this->original    = [];
			$this->related     = [];

			unset($this->columns[$this->primaryKey]);
		}
	}

	/**
	 * Set the connection manager.
	 */
	public static function setConnectionManager(ConnectionManager $connectionManager): void
	{
		static::$connectionManager = $connectionManager;
	}

	/**
	 * Returns the connection of the model.
	 */
	public function getConnection(): Connection
	{
		if (empty(static::$connectionManager)) {
			static::$connectionManager = CurrentApplication::get()?->getContainer()->get(ConnectionManager::class);
		}

		return static::$connectionManager->getConnection($this->connectionName);
	}

	/**
	 * Has the record been loaded from/saved to a database?
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
	 */
	protected function getDateFormat(): string
	{
		static $dateFormat = [];

		return $dateFormat[static::class] ?? ($dateFormat[static::class] = $this->getQuery()->getCompiler()->getDateFormat());
	}

	/**
	 * Registers hooks and casts.
	 */
	protected function registerHooksAndCasts(): void
	{
		if (!isset(static::$traitHooks[static::class])) {
			static::$traitHooks[static::class] = [];
			static::$traitCasts[static::class] = [];

			foreach (ClassInspector::getTraits(static::class) as $trait) {
				if (method_exists($this, $getter = "get{$this->getClassShortName($trait)}Hooks")) {
					static::$traitHooks[static::class] = array_merge_recursive(static::$traitHooks[static::class], $this->{$getter}());
				}

				if (method_exists($this, $getter = "get{$this->getClassShortName($trait)}Casts")) {
					static::$traitCasts[static::class] += $this->{$getter}();
				}
			}
		}

		$this->cast += static::$traitCasts[static::class];
	}

	/**
	 * Binds the hooks to the current instance of "$this".
	 */
	protected function bindHooks(array $hooks): array
	{
		$bound = [];

		foreach ($hooks as $hook) {
			$bound[] = $hook->bindTo($this);
		}

		return $bound;
	}

	/**
	 * Returns hooks for the chosen event.
	 */
	public function getHooks(string $event): array
	{
		return isset(static::$traitHooks[static::class][$event]) ? $this->bindHooks(static::$traitHooks[static::class][$event]) : [];
	}

	/**
	 * Returns the short name of a class.
	 */
	protected function getClassShortName(?string $className = null): string
	{
		$class = $className ?? static::class;

		$pos = strrpos($class, '\\');

		if ($pos === false) {
			return $class;
		}

		return substr($class, $pos + 1);
	}

	/**
	 * Returns the table name of the model.
	 */
	public function getTable(): string
	{
		if (empty($this->tableName)) {
			$this->tableName = Str::pluralize(Str::camelToSnake($this->getClassShortName()));
		}

		return $this->tableName;
	}

	/**
	 * Returns the primary key of the table.
	 */
	public function getPrimaryKey(): string
	{
		return $this->primaryKey;
	}

	/**
	 * Returns the primary key type.
	 */
	public function getPrimaryKeyType(): int
	{
		return $this->primaryKeyType;
	}

	/**
	 * Returns the primary key value.
	 */
	public function getPrimaryKeyValue(): mixed
	{
		return $this->columns[$this->primaryKey];
	}

	/**
	 * Returns the foreign key of the table.
	 */
	public function getForeignKey(): string
	{
		if (empty($this->foreignKeyName)) {
			$this->foreignKeyName = Str::camelToSnake($this->getClassShortName()) . '_id';
		}

		return $this->foreignKeyName;
	}

	/**
	 * Returns the namespaced class name of the model.
	 *
	 * @return class-string<static>
	 */
	public function getClass(): string
	{
		return '\\' . static::class;
	}

	/**
	 * Sets the relations to eager load.
	 */
	public function setIncludes(array $includes): void
	{
		$this->including = $includes;
	}

	/**
	 * Returns the relations to eager load.
	 */
	public function getIncludes(): array
	{
		return $this->including;
	}

	/**
	 * Sets eagerly loaded related records.
	 */
	public function setRelated(string $relation, mixed $related): void
	{
		$this->related[$relation] = $related;
	}

	/**
	 * Returns TRUE if the model has included the relationship and FALSE if not.
	 */
	public function includes(string $relation): bool
	{
		return array_key_exists($relation, $this->related);
	}

	/**
	 * Eager loads relations on the model.
	 *
	 * @return $this
	 */
	public function include(array|string $includes): static
	{
		(function ($includes, $model): void {
			$this->including($includes)->loadIncludes([$model]);
		})->bindTo($this->getQuery(), Query::class)($includes, $this);

		return $this;
	}

	/**
	 * Returns the related records array.
	 */
	public function getRelated(): array
	{
		return $this->related;
	}

	/**
	 * Cast value to the appropriate type.
	 */
	protected function cast(string $name, mixed $value): mixed
	{
		if (isset($this->cast[$name]) && $value !== null) {
			$type  = $this->cast[$name];
			$extra = null;

			if (is_array($type)) {
				$extra = $this->cast[$name][$type = array_key_first($type)];
			}

			return match ($type) {
				'int'    => (int) $value,
				'float'  => (float) $value,
				'bool'   => $value === 'f' ? false : (bool) $value,
				'date'   => ($value instanceof DateTimeInterface) ? $value : Time::createFromFormat($this->getDateFormat(), $value),
				'string' => (string) $value,
				'enum'   => is_object($value) ? $value : $extra::from($value),
				default  => throw new DatabaseException(sprintf('Unsupported type [ %s ].', $type)),
			};
		}

		return $value;
	}

	/**
	 * Sets a raw column value.
	 */
	public function setRawColumnValue(string $name, mixed $value): void
	{
		$this->columns[$name] = $this->cast($name, $value);
	}

	/**
	 * Sets a column value.
	 */
	public function setColumnValue(string $name, mixed $value): void
	{
		$value = $this->cast($name, $value);

		if (method_exists($this, "{$name}Mutator")) {
			$this->columns[$name] = $this->{"{$name}Mutator"}($value);
		}
		else {
			$this->columns[$name] = $value;
		}
	}

	/**
	 * Gets a raw column value.
	 */
	public function getRawColumnValue(string $name): mixed
	{
		return $this->columns[$name];
	}

	/**
	 * Returns a column value.
	 */
	public function getColumnValue(string $name): mixed
	{
		if (method_exists($this, "{$name}Accessor")) {
			return $this->{"{$name}Accessor"}($this->columns[$name]);
		}

		return $this->columns[$name];
	}

	/**
	 * Returns TRUE if it's probable that $name is a relation and FALSE if not.
	 */
	protected function isRelation(string $name): bool
	{
		return method_exists(self::class, $name) === false && method_exists($this, $name);
	}

	/**
	 * Gets a column value or relation.
	 */
	public function getValue(string $name): mixed
	{
		if (array_key_exists($name, $this->columns)) {
			// It's a database column

			return $this->getColumnValue($name);
		}
		elseif (array_key_exists($name, $this->related)) {
			// The column is a cached or eagerly loaded relation

			return $this->related[$name];
		}
		elseif ($this->isRelation($name)) {
			// The column is a relation. Lazy load the record(s) and cache them

			return $this->related[$name] = $this->{$name}()->getRelated();
		}

		// All options have been exhausted so we'll throw an exception

		throw new DatabaseException(sprintf('Unknown column or relation [ %s ].', $name));
	}

	/**
	 * Returns the columns array.
	 */
	public function getRawColumnValues(): array
	{
		return $this->columns;
	}

	/**
	 * Sets column values.
	 */
	protected function setColumValues(array $columns, bool $raw): void
	{
		if ($raw) {
			if (empty($this->cast)) {
				$this->columns = $columns;
			}
			else {
				foreach ($columns as $column => $value) {
					$this->setRawColumnValue($column, $value);
				}
			}
		}
		else {
			foreach ($columns as $column => $value) {
				$this->setColumnValue($column, $value);
			}
		}
	}

	/**
	 * Assigns the column values to the model.
	 *
	 * @return $this
	 */
	public function assign(array $columns, bool $raw = false, bool $whitelist = true): static
	{
		// Remove columns that are not in the whitelist

		if ($whitelist && !empty($this->assignable)) {
			$columns = array_intersect_key($columns, array_flip($this->assignable));
		}

		// Remove the primary key if the model has already beed loaded

		if ($this->isPersisted && isset($columns[$this->primaryKey])) {
			unset($columns[$this->primaryKey]);
		}

		// Set column values

		$this->setColumValues($columns, $raw);

		return $this;
	}

	/**
	 * Set column value using overloading.
	 */
	public function __set(string $name, mixed $value): void
	{
		$this->setColumnValue($name, $value);
	}

	/**
	 * Get column value or relation using overloading.
	 */
	public function __get(string $name): mixed
	{
		return $this->getValue($name);
	}

	/**
	 * Checks if a column or relation is set using overloading.
	 */
	public function __isset(string $name): bool
	{
		if (isset($this->columns[$name]) || isset($this->related[$name])) {
			return true;
		}

		return $this->isRelation($name) && $this->getValue($name) !== null;
	}

	/**
	 * Unset column value or relation using overloading.
	 */
	public function __unset(string $name): void
	{
		unset($this->columns[$name], $this->related[$name]);
	}

	/**
	 * Returns a query builder instance.
	 */
	public function getQuery(): Query
	{
		return new Query($this->getConnection(), $this);
	}

	/**
	 * Returns a record using the value of its primary key.
	 */
	public static function get(mixed $id, array $columns = []): ?static
	{
		return (new static)->getQuery()->get($id, $columns);
	}

	/**
	 * Returns a record using the value of its primary key.
	 */
	public static function getOrThrow(mixed $id, array $columns = [], string $exception = NotFoundException::class): static
	{
		return (new static)->getQuery()->getOrThrow($id, $columns, $exception);
	}

	/**
	 * Creates a new record and returns the model.
	 */
	public static function create(array $columns = [], bool $raw = false, bool $whitelist = true): static
	{
		$model = new static($columns, $raw, $whitelist);

		$model->save();

		return $model;
	}

	/**
	 * Returns a HasOne relation.
	 */
	protected function hasOne(string $model, ?string $foreignKey = null): HasOne
	{
		$related = new $model;

		return new HasOne($related->getConnection(), $this, $related, $foreignKey);
	}

	/**
	 * Returns a HasOnePolymorphic relation.
	 */
	protected function hasOnePolymorphic(string $model, string $polymorphicType): HasOnePolymorphic
	{
		$related = new $model;

		return new HasOnePolymorphic($related->getConnection(), $this, $related, $polymorphicType);
	}

	/**
	 * Returns a HasMany relation.
	 */
	protected function hasMany(string $model, ?string $foreignKey = null): HasMany
	{
		$related = new $model;

		return new HasMany($related->getConnection(), $this, $related, $foreignKey);
	}

	/**
	 * Returns a HasManyPolymorphic relation.
	 */
	protected function hasManyPolymorphic(string $model, string $polymorphicType): HasManyPolymorphic
	{
		$related = new $model;

		return new HasManyPolymorphic($related->getConnection(), $this, $related, $polymorphicType);
	}

	/**
	 * Returns a ManyToMany relation.
	 */
	protected function manyToMany(string $model, ?string $foreignKey = null, ?string $junctionTable = null, ?string $junctionKey = null): ManyToMany
	{
		$related = new $model;

		return new ManyToMany($related->getConnection(), $this, $related, $foreignKey, $junctionTable, $junctionKey);
	}

	/**
	 * Returns a BelongsTo relation.
	 */
	protected function belongsTo(string $model, ?string $foreignKey = null): BelongsTo
	{
		$related = new $model;

		return new BelongsTo($related->getConnection(), $this, $related, $foreignKey);
	}

	/**
	 * Returns a BelongsToPolymorphic relation.
	 */
	protected function belongsToPolymorphic(string $model, string $polymorphicType): BelongsToPolymorphic
	{
		$related = new $model;

		return new BelongsToPolymorphic($related->getConnection(), $this, $related, $polymorphicType);
	}

	/**
	 * Has the record been modified?
	 */
	public function isModified(): bool
	{
		return count($this->getModified()) > 0;
	}

	/**
	 * Returns the modified column values of the record.
	 */
	public function getModified(): array
	{
		$modified = [];

		foreach ($this->columns as $key => $value) {
			if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
				$modified[$key] = $value;
			}
		}

		return $modified;
	}

	/**
	 * Generates a primary key.
	 */
	protected function generatePrimaryKey(): mixed
	{
		throw new DatabaseException(sprintf('The [ %s::generatePrimaryKey() ] method must be implemented.', static::class));
	}

	/**
	 * Inserts a new record into the database.
	 */
	protected function insertRecord(Query $query): void
	{
		if ($this->primaryKeyType === static::PRIMARY_KEY_TYPE_INCREMENTING) {
			$this->columns[$this->primaryKey] = $query->insertAndGetId($this->columns, $this->primaryKey);

			return;
		}

		if ($this->primaryKeyType !== static::PRIMARY_KEY_TYPE_NONE) {
			$this->columns[$this->primaryKey] = match ($this->primaryKeyType) {
				static::PRIMARY_KEY_TYPE_UUID   => UUID::v4Sequential(),
				static::PRIMARY_KEY_TYPE_CUSTOM => $this->generatePrimaryKey(),
				default                         => throw new DatabaseException('Invalid primary key type.'),
			};
		}

		$query->insert($this->columns);
	}

	/**
	 * Updates an existing record.
	 */
	protected function updateRecord(Query $query): bool
	{
		$query->where($this->primaryKey, '=', $this->columns[$this->primaryKey]);

		return (bool) $query->update($this->getModified());
	}

	/**
	 * Saves the record to the database.
	 */
	public function save(): bool
	{
		$success = true;

		if (!$this->isPersisted) {
			// This is a new record so we need to insert it into the database.

			$this->insertRecord($this->getQuery());

			$this->isPersisted = true;
		}
		elseif ($this->isModified()) {
			// This record exists and is modified so all we have to do is update it.

			$success = $this->updateRecord($this->getQuery());
		}

		if ($success) {
			// Sync up if save was successful

			$this->synchronize();
		}

		return $success;
	}

	/**
	 * Deletes a record from the database.
	 */
	protected function deleteRecord(Query $query): bool
	{
		return (bool) $query->where($this->primaryKey, '=', $this->columns[$this->primaryKey])->delete();
	}

	/**
	 * Deletes a record from the database.
	 */
	public function delete(): bool
	{
		if ($this->isPersisted) {
			$deleted = $this->deleteRecord($this->getQuery());

			if ($deleted) {
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
	 * @return $this
	 */
	public function protect(array|false|string $column): static
	{
		$this->protected = $column === false ? [] : array_unique([...$this->protected, ...(array) $column]);

		return $this;
	}

	/**
	 * Exposes the chosen columns and relations in the array and json representations of the record.
	 * You can expose all fields by passing TRUE.
	 *
	 * @return $this
	 */
	public function expose(array|string|true $column): static
	{
		$this->protected = $column === true ? [] : array_diff($this->protected, (array) $column);

		return $this;
	}

	/**
	 * Returns an array representation of the record.
	 */
	public function toArray(): array
	{
		$columns = $this->columns;

		// Removes protected columns from the array

		if (!empty($this->protected)) {
			$columns = array_diff_key($columns, array_flip($this->protected));
		}

		// Mutate column values if needed

		foreach ($columns as $name => $value) {
			$value = $this->getColumnValue($name);

			if ($value instanceof DateTimeInterface) {
				$value = $value->format($this->dateOutputFormat);
			}

			$columns[$name] = $value;
		}

		// Merge related records

		foreach ($this->related as $relation => $related) {
			if (in_array($relation, $this->protected)) {
				continue;
			}

			if ($related === null) {
				$columns += [$relation => null];

				continue;
			}

			if (!empty($this->protected)) {
				$protect = array_map(static fn ($value) => substr($value, strlen($relation) + 1),
					array_filter($this->protected, static fn ($value) => str_starts_with($value, "{$relation}."))
				);

				if (!empty($protect)) {
					$related->protect($protect);
				}
			}

			$columns += [$relation => $related->toArray()];
		}

		// Returns array representation of the record

		return $columns;
	}

	/**
	 * Returns data which can be serialized by json_encode().
	 */
	#[Override]
	public function jsonSerialize(): mixed
	{
		return $this->toArray();
	}

	/**
	 * Returns a json representation of the record.
	 */
	public function toJson(int $options = 0): string
	{
		return json_encode($this->toArray(), $options);
	}

	/**
	 * Returns a json representation of the record.
	 */
	#[Override]
	public function __toString(): string
	{
		return $this->toJson();
	}

	/**
	 * Forwards method calls to the query builder.
	 */
	public function __call(string $name, array $arguments): mixed
	{
		return $this->getQuery()->{$name}(...$arguments);
	}

	/**
	 * Forwards static method calls to the query builder.
	 */
	public static function __callStatic(string $name, array $arguments): mixed
	{
		return (new static)->getQuery()->{$name}(...$arguments);
	}
}
