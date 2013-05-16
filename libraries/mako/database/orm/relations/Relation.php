<?php

namespace mako\database\orm\relations;

/**
 * Base relation.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2012 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

abstract class Relation extends \mako\database\orm\Hydrator
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

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

	// Nothing here

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
	 * @return  \mako\database\orm\relations\Relation 
	 */

	protected function lazyCriterion()
	{
		$this->query->where($this->getForeignKey(), '=', $this->parent->getPrimaryKeyValue());

		return $this;
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

		$this->query->in($this->getForeignKey(), $keys);

		return $this;
	}

	/**
	 * Returns a single record from the database.
	 * 
	 * @access  public
	 * @return  \mako\database\ORM
	 */

	public function first()
	{
		if($this->lazy)
		{
			$this->lazyCriterion();
		}

		return parent::first();
	}

	/**
	 * Returns a result set from the database.
	 * 
	 * @access  public
	 * @return  \mako\database\orm\ResultSet
	 */

	public function all()
	{
		if($this->lazy)
		{
			$this->lazyCriterion();
		}

		return parent::all();
	}

	/**
	 * Forwards method calls to the query builder instance.
	 * 
	 * @access  public
	 * @param   string  $name       Method name
	 * @param   array   $arguments  Method arguments
	 * @return  mixed
	 */

	public function __call($name, $arguments)
	{
		$name = strtolower($name);
		
		if(in_array($name, array('count', 'min', 'max', 'avg', 'column', 'delete', 'update', 'increment', 'decrement')))
		{
			// Set the lazy criterion to make sure these methods are executed on the related records

			$this->lazyCriterion();
		}
		
		return parent::__call($name, $arguments);
	}
}

/** -------------------- End of file --------------------**/