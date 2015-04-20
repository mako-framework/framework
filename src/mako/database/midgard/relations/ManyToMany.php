<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

use mako\database\Connection;
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
	 * @param   \mako\database\Connection   $connection     Database connection
	 * @param   \mako\database\midgard\ORM  $parent         Parent model
	 * @param   \mako\database\midgard\ORM  $related        Related model
	 * @param   string|null                 $foreignKey     Foreign key name
	 * @param   string|null                 $junctionTable  Junction table name
	 * @param   string|null                 $junctionKey    Junction key name
	 */

	public function __construct(Connection $connection, ORM $parent, ORM $related, $foreignKey = null, $junctionTable = null, $junctionKey = null)
	{
		$this->junctionTable = $junctionTable;

		$this->junctionKey = $junctionKey;

		parent::__construct($connection, $parent, $related, $foreignKey);

		$this->junctionJoin();
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

		foreach($this->eagerLoadChunked($this->keys($results)) as $related)
		{
			$grouped[$related->getRawColumn($this->getForeignKey())][] = $related;

			unset($related->{$this->getForeignKey()}); // Unset as its not a part of the record
		}

		foreach($results as $result)
		{
			if(isset($grouped[$result->getPrimaryKeyValue()]))
			{
				$result->setRelated($relation, new ResultSet($grouped[$result->getPrimaryKeyValue()]));
			}
			else
			{
				$result->setRelated($relation, new ResultSet());
			}
		}
	}

	/**
	 * Adjusts the column selection.
	 *
	 * @access  protected
	 * @param   array      $columns  Columns
	 * @return  array
	 */

	protected function adjustSelection(array $columns)
	{
		if($columns === ['*'])
		{
			$columns = [$this->model->getTable() . '.*'];
		}

		if(!$this->lazy)
		{
			$columns = array_merge($columns, [$this->getJunctionTable() . '.' . $this->getForeignKey()]);
		}

		return $columns;
	}

	/**
	 * Returns a single record from the database.
	 *
	 * @access  public
	 * @return  \mako\database\midgard\ORM
	 */

	public function first()
	{
		$this->columns = $this->adjustSelection($this->columns);

		return parent::first();
	}

	/**
	 * Returns a result set from the database.
	 *
	 * @access  public
	 * @return  \mako\database\midgard\ResultSet
	 */

	public function all()
	{
		$this->columns = $this->adjustSelection($this->columns);

		return parent::all();
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
	 * @param   mixed    $id  Id or model
	 * @return  boolean
	 */

	public function link($id)
	{
		if($id instanceof $this->model)
		{
			$id = $id->getPrimaryKeyValue();
		}

		if($this->junction()->where($this->getForeignKey(), '=', $this->parent->getPrimaryKeyValue())->where($this->getJunctionKey(), '=', $id)->count() == 0)
		{
			return $this->junction()->insert([$this->getForeignKey() => $this->parent->getPrimaryKeyValue(), $this->getJunctionKey() => $id]);
		}

		return false;
	}

	/**
	 * Unlinks related records.
	 *
	 * @access  public
	 * @param   mixed    $id  Id or model
	 * @return  boolean
	 */

	public function unlink($id = null)
	{
		$query = $this->junction()->where($this->getForeignKey(), '=', $this->parent->getPrimaryKeyValue());

		if($id !== null)
		{
			if($id instanceof $this->model)
			{
				$id = $id->getPrimaryKeyValue();
			}

			$query->where($this->getJunctionKey(), '=', $id);
		}

		return (bool) $query->delete();
	}
}