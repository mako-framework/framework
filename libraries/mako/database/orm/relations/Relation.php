<?php

namespace mako\database\orm\relations;

use \mako\database\Connection;
use \mako\database\ORM;

/**
 * Base relation.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

abstract class Relation extends \mako\database\orm\Hydrator
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Parent record.
	 * 
	 * @var \mako\database\ORM
	 */

	protected $parent;

	/**
	 * Foreign key.
	 * 
	 * @var string
	 */

	protected $foreignKey = null;

	/**
	 * Lazy load related records?
	 * 
	 * @var boolean
	 */

	protected $lazy = true;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\database\Connection  $connection  Database connection
	 * @param   \mako\database\ORM         $parent      Parent model
	 * @param   \mako\database\ORM         $related     Related model
	 */

	public function __construct(Connection $connection, ORM $parent, ORM $related)
	{
		$this->parent = $parent;

		parent::__construct($connection, $related);

	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Sets the foreign key.
	 * 
	 * @access  public
	 * @param   string                       $foreignKey  Foreign key
	 * @return  \mako\database\orm\Relation
	 */

	public function setForeignKey($foreignKey)
	{
		$this->foreignKey = $foreignKey;

		return $this;
	}

	/**
	 * Returns the foreign key.
	 * 
	 * @access  protected
	 * @return  string
	 */

	protected function getForeignKey()
	{
		if($this->foreignKey === null)
		{
			$this->foreignKey = $this->parent->getForeignKey();
		}

		return $this->foreignKey;
	}

	/**
	 * Returns the keys used to eagerly load records.
	 * 
	 * @access  protected
	 * @param   \mako\database\orm\ResultSet  $results  Result set
	 * @return  array
	 */

	protected function keys($results)
	{
		$keys = array();

		foreach($results as $result)
		{
			$keys[] = $result->getPrimaryKeyValue();
		}

		return array_unique($keys);
	}

	/**
	 * Sets the criterion used when lazy loading related records.
	 * 
	 * @access  protected
	 */

	protected function lazyCriterion()
	{
		$this->where($this->getForeignKey(), '=', $this->parent->getPrimaryKeyValue());
	}

	/**
	 * Sets the criterion used when eager loading related records.
	 * 
	 * @access  protected
	 * @param   array                                  $keys  Parent keys
	 * @return  \mako\database\orm\relations\Relation
	 */

	protected function eagerCriterion($keys)
	{
		$this->lazy = false;

		$this->in($this->getForeignKey(), $keys);

		return $this;
	}

	/**
	 * Returns a single record from the database.
	 * 
	 * @access  public
	 * @param   array               $columns  (optional) Columns to select
	 * @return  \mako\database\ORM
	 */

	public function first(array $columns = array())
	{
		if($this->lazy)
		{
			$this->lazyCriterion();
		}

		return parent::first($columns);
	}

	/**
	 * Returns a result set from the database.
	 * 
	 * @access  public
	 * @param   array                         $columns  (optional) Columns to select
	 * @return  \mako\database\orm\ResultSet
	 */

	public function all(array $columns = array())
	{
		if($this->lazy)
		{
			$this->lazyCriterion();
		}

		return parent::all($columns);
	}

	/**
	 * Returns the value of the chosen column of the first row of the result set.
	 *
	 * @access  public
	 * @param   string   $column  Column to select
	 * @return  mixed
	 */

	public function column($column)
	{
		$this->lazyCriterion();

		return parent::column($column);
	}

	/**
	 * Updates data from the chosen table.
	 *
	 * @access  public
	 * @param   array    $values  Associative array of column values
	 * @return  int
	 */

	public function update(array $values)
	{
		$this->lazyCriterion();

		return parent::update($values);
	}

	/**
	 * Deletes data from the chosen table.
	 *
	 * @access  public
	 * @return  int
	 */

	public function delete()
	{
		$this->lazyCriterion();

		return parent::delete();
	}
}

/** -------------------- End of file --------------------**/