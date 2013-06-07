<?php

namespace mako\database\orm\relations;

use \mako\database\Connection;
use \mako\database\ORM;
use \mako\database\orm\ResultSet;

/**
 * Many to many relation.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class ManyToMany extends \mako\database\orm\relations\Relation
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

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

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\database\Connection  $connection     Database connection
	 * @param   \mako\database\ORM         $parent         Parent model
	 * @param   \mako\database\ORM         $related        Related model
	 * @param   string|null                $foreignKey     (optional) Foreign key name
	 * @param   string|null                $junctionTable  (optional) Junction table name
	 * @param   string|null                $junctionKey    (optional) Junction key name
	 */

	public function __construct(Connection $connection, ORM $parent, ORM $related, $foreignKey = null, $junctionTable = null, $junctionKey = null)
	{
		$this->junctionTable = $junctionTable;

		$this->junctionKey = $junctionKey;

		parent::__construct($connection, $parent, $related, $foreignKey);

		$this->junctionJoin();
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

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
			$tables = array($this->parent->getTable(), $this->model->getTable());

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
	 * Sets the criterion used when lazy loading related records.
	 * 
	 * @access  protected
	 */

	protected function lazyCriterion()
	{
		$this->where($this->getJunctionTable() . '.' . $this->getForeignKey(), '=', $this->parent->getPrimaryKeyValue());
	}

	/**
	 * Sets the criterion used when eager loading related records.
	 * 
	 * @access  protected
	 * @param   array                                 $keys  Parent keys
	 * @return  \mako\database\orm\relations\HasMany
	 */

	protected function eagerCriterion($keys)
	{
		$this->lazy = false;

		$this->in($this->getJunctionTable() . '.' . $this->getForeignKey(), $keys);

		return $this;
	}

	/**
	 * Eager loads related records and matches them with their parent records.
	 * 
	 * @access  public
	 * @param   \mako\database\orm\ResultSet  $results   Parent records
	 * @param   string                        $relation  Relation name
	 * @param   mixed                         $criteria  Relation criteria
	 * @param   array                         $includes  Includes passed from the parent record
	 */

	public function eagerLoad(&$results, $relation, $criteria, $includes)
	{
		$this->model->setIncludes($includes);

		$grouped = array();

		if($criteria !== null)
		{
			$criteria($this);
		}

		foreach($this->eagerCriterion($this->keys($results))->all() as $related)
		{
			$grouped[$related->getColumn($this->getForeignKey())][] = $related;

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
	 * Returns the columns to fetch.
	 * 
	 * @access  protected
	 * @return  array
	 */

	protected function select()
	{
		if($this->lazy)
		{
			return array($this->model->getTable() . '.*');
		}
		else
		{
			return array($this->model->getTable() . '.*', $this->getJunctionTable() . '.' . $this->getForeignKey());
		}
	}

	/**
	 * Returns a single record from the database.
	 * 
	 * @access  public
	 * @return  \mako\database\ORM
	 */

	public function first(array $columns = array())
	{
		$this->columns = $this->select();

		return parent::first($columns);
	}

	/**
	 * Returns a result set from the database.
	 * 
	 * @access  public
	 * @return  \mako\database\orm\ResultSet
	 */

	public function all(array $columns = array())
	{
		$this->columns = $this->select();

		return parent::all($columns);
	}

	/**
	 * Returns a result set from the database.
	 * 
	 * @access  public
	 * @return  \mako\database\ORM
	 */ 

	public function get()
	{
		return $this->all();
	}

	/**
	 * Returns a query builder instance to the junction table.
	 * 
	 * @access  protected
	 * @return  \mako\database\Query
	 */

	protected function junction()
	{
		return $this->connection->table($this->getJunctionTable());
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
			return $this->junction()->insert(array($this->getForeignKey() => $this->parent->getPrimaryKeyValue(), $this->getJunctionKey() => $id));
		}

		return false;
	}

	/**
	 * Unlinks related records.
	 * 
	 * @access  public
	 * @param   mixed    $id  (optional) Id or model
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

/** -------------------- End of file -------------------- **/