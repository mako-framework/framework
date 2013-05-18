<?php

namespace mako\database\orm\relations;

use \mako\database\Connection;
use \mako\database\ORM;

/**
 * Base relation.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2012 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

abstract class Relation extends \mako\database\orm\Query
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

		$this->lazyCriterion();
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
			$this->foreignKey = $this->parent->getTable(true) . '_id';
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
		if(!$this->lazy)
		{
			array_shift($this->wheres);
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
		if(!$this->lazy)
		{
			array_shift($this->wheres);
		}

		return parent::all($columns);
	}
}

/** -------------------- End of file --------------------**/