<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

use mako\database\connections\Connection;
use mako\database\midgard\ORM;
use mako\database\midgard\ResultSet;
use mako\database\midgard\relations\Relation;

/**
 * Many to many relation.
 *
 * @author  Frederic G. Østby
 */
class ManyToMany extends Relation
{
	/**
	 * Junction table.
	 *
	 * @var string
	 */
	protected $junctionTable = null;

	/**
	 * Junction key.
	 *
	 * @var string
	 */
	protected $junctionKey = null;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\database\connections\Connection  $connection     Database connection
	 * @param   \mako\database\midgard\ORM             $parent         Parent model
	 * @param   \mako\database\midgard\ORM             $related        Related model
	 * @param   string|null                            $foreignKey     Foreign key name
	 * @param   string|null                            $junctionTable  Junction table name
	 * @param   string|null                            $junctionKey    Junction key name
	 */
	public function __construct(Connection $connection, ORM $parent, ORM $related, $foreignKey = null, $junctionTable = null, $junctionKey = null)
	{
		$this->junctionTable = $junctionTable;

		$this->junctionKey = $junctionKey;

		parent::__construct($connection, $parent, $related, $foreignKey);

		$this->junctionJoin();

		$this->columns = [$this->model->getTable() . '.*'];
	}

	/**
	 * Returns the the junction table.
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function getJunctionTable()
	{
		if($this->junctionTable === null)
		{
			$tables = [$this->parent->getTable(), $this->model->getTable()];

			sort($tables);

			$this->junctionTable = implode('_', $tables);
		}

		return $this->junctionTable;
	}

	/**
	 * Returns the the junction key.
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function getJunctionKey()
	{
		if($this->junctionKey === null)
		{
			$this->junctionKey = $this->model->getForeignKey();
		}

		return $this->junctionKey;
	}

	/**
	 * Joins the junction table.
	 *
	 * @access  protected
	 */
	protected function junctionJoin()
	{
		$this->join($this->getJunctionTable(), $this->getJunctionTable() . '.' . $this->getJunctionKey(), '=', $this->model->getTable() . '.' . $this->model->getPrimaryKey());
	}

	/**
	 * {@inheritdoc}
	 */
	protected function lazyCriterion()
	{
		$this->where($this->getJunctionTable() . '.' . $this->getForeignKey(), '=', $this->parent->getPrimaryKeyValue());
	}

	/**
	 * Sets the criterion used when eager loading related records.
	 *
	 * @access  protected
	 * @param   array                                     $keys  Parent keys
	 * @return  \mako\database\midgard\relations\HasMany
	 */
	protected function eagerCriterion(array $keys)
	{
		$this->lazy = false;

		$this->in($this->getJunctionTable() . '.' . $this->getForeignKey(), $keys);

		return $this;
	}

	/**
	 * Eager loads related records and matches them with their parent records.
	 *
	 * @access  public
	 * @param   array          $results   Parent records
	 * @param   string         $relation  Relation name
	 * @param   null|\Closure  $criteria  Relation criteria
	 * @param   array          $includes  Includes passed from the parent record
	 */
	public function eagerLoad(array &$results, $relation, $criteria, array $includes)
	{
		$this->model->setIncludes($includes);

		$grouped = [];

		if($criteria !== null)
		{
			$criteria($this);
		}

		$foreignKey = $this->getForeignKey();

		foreach($this->eagerLoadChunked($this->keys($results)) as $related)
		{
			$grouped[$related->getRawColumn($foreignKey)][] = $related;

			unset($related->{$foreignKey}); // Unset as its not a part of the record
		}

		foreach($results as $result)
		{
			$result->setRelated($relation, $this->createResultSet($grouped[$result->getPrimaryKeyValue()] ?? []));
		}
	}

	/**
	 * Adjusts the query.
	 *
	 * @access  protected
	 */
	protected function adjustQuery()
	{
		if(!$this->lazy)
		{
			$this->columns = array_merge($this->columns, [$this->getJunctionTable() . '.' . $this->getForeignKey()]);
		}

		parent::adjustQuery();
	}

	/**
	 * Returns a related result set from the database.
	 *
	 * @access  public
	 * @return  \mako\database\midgard\ORM
	 */
	public function getRelated()
	{
		return $this->all();
	}

	/**
	 * Returns a query builder instance to the junction table.
	 *
	 * @access  protected
	 * @return  \mako\database\query\Query
	 */
	protected function junction()
	{
		return $this->connection->builder()->table($this->getJunctionTable());
	}

	/**
	 * Links related records.
	 *
	 * @access  public
	 * @param   mixed   $id  Id, model or an array of ids and/or models
	 * @return  bool
	 */
	public function link($id)
	{
		$success = true;

		foreach((is_array($id) ? $id : [$id]) as $value)
		{
			if($value instanceof $this->model)
			{
				$value = $value->getPrimaryKeyValue();
			}

			$success = $success && $this->junction()->insert([$this->getForeignKey() => $this->parent->getPrimaryKeyValue(), $this->getJunctionKey() => $value]);
		}

		return $success;
	}

	/**
	 * Unlinks related records.
	 *
	 * @access  public
	 * @param   mixed   $id  Id, model or an array of ids and/or models
	 * @return  bool
	 */
	public function unlink($id = null)
	{
		$query = $this->junction()->where($this->getForeignKey(), '=', $this->parent->getPrimaryKeyValue());

		if($id !== null)
		{
			$keys = [];

			foreach((is_array($id) ? $id : [$id]) as $value)
			{
				if($value instanceof $this->model)
				{
					$value = $value->getPrimaryKeyValue();
				}

				$keys[] = $value;
			}

			$query->in($this->getJunctionKey(), $keys);
		}

		return (bool) $query->delete();
	}

	/**
	 * Synchronize related records.
	 *
	 * @access  public
	 * @param   array   $ids  An array of ids and/or models
	 * @return  bool
	 */
	public function synchronize(array $ids)
	{
		$success = true;

		$keys = [];

		foreach($ids as $value)
		{
			if($value instanceof $this->model)
			{
				$value = $value->getPrimaryKeyValue();
			}

			$keys[] = $value;
		}

		// Fetch existing links

		$existing = $this->junction()->where($this->getForeignKey(), '=', $this->parent->getPrimaryKeyValue())->select([$this->getJunctionKey()])->all()->pluck($this->getJunctionKey());

		// Link new relations

		$success = $success && $this->link(array_diff($keys, $existing));

		// Unlink old relations

		$success = $success && $this->unlink(array_diff($existing, $keys));

		// Return status

		return $success;
	}
}
