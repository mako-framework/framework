<?php

namespace mako\database\midgard\relations;

use \mako\database\Connection;
use \mako\database\midgard\ORM;

/**
 * Base relation.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

abstract class Relation extends \mako\database\midgard\Hydrator
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Parent record.
	 * 
	 * @var \mako\database\midgard\ORM
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
	 * @param   \mako\database\Connection   $connection  Database connection
	 * @param   \mako\database\midgard\ORM  $parent      Parent model
	 * @param   \mako\database\midgard\ORM  $related     Related model
	 * @param   string|null                 $foreignKey  (optional) Foreign key name
	 */

	public function __construct(Connection $connection, ORM $parent, ORM $related, $foreignKey = null)
	{
		$this->parent = $parent;

		$this->foreignKey = $foreignKey;

		parent::__construct($connection, $related);

		$this->lazyCriterion();
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

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
	 * @param   \mako\database\midgard\ResultSet  $results  Result set
	 * @return  array
	 */

	protected function keys($results)
	{
		$keys = [];

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
	 * @param   array                                      $keys  Parent keys
	 * @return  \mako\database\midgard\relations\Relation
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
	 * @param   array                       $columns  (optional) Columns to select
	 * @return  \mako\database\midgard\ORM
	 */

	public function first(array $columns = [])
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
	 * @param   array                             $columns  (optional) Columns to select
	 * @return  \mako\database\midgard\ResultSet
	 */

	public function all(array $columns = [])
	{
		if(!$this->lazy)
		{
			array_shift($this->wheres);
		}

		return parent::all($columns);
	}
}

/** -------------------- End of file -------------------- **/