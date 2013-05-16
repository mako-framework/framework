<?php

namespace mako\database\orm;

use \mako\Database;
use \mako\database\orm\ResultSet;

/**
 * Model hydrator.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2012 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Hydrator
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Instance of the model to hydrate.
	 * 
	 * @var \mako\database\ORM
	 */

	protected $model;

	/**
	 * Query builder instance.
	 * 
	 * @var \mako\database\Query
	 */

	protected $query;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\database\ORM  $model  Model to hydrate
	 */

	public function __construct(\mako\database\ORM $model)
	{
		$this->model = $model;

		$this->query = $this->query();
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns a query builder instance.
	 * 
	 * @access  protected
	 * @return  \mako\database\Query
	 */

	protected function query()
	{
		return Database::connection($this->model->getConnection())->table($this->model->getTable());
	}

	/**
	 * Sets the relations to eager load.
	 * 
	 * @access  public
	 * @param   mixed                        $includes  Relation or array of relations to eager load
	 * @return  \mako\database\orm\Hydrator
	 */

	public function including($includes)
	{
		$this->model->setIncludes((array) $includes);

		return $this;
	}

	/**
	 * Removes relations to eager load.
	 * 
	 * @access  public
	 * @param   mixed                        $excludes  Relation or array of relations to exclude from eager loading
	 * @return  \mako\database\orm\Hydrator
	 */

	public function excluding($excludes)
	{
		$this->model->setIncludes(array_diff($this->model->getIncludes(), (array) $excludes));

		return $this;
	}

	/**
	 * Parses includes.
	 * 
	 * @access  protected
	 * @return  array
	 */

	protected function parseIncludes()
	{
		$includes = array('this' => array(), 'forward' => array());

		foreach($this->model->getIncludes() as $include => $criteria)
		{
			if(is_numeric($include))
			{
				$include  = $criteria;
				$criteria = null;
			}

			if(($position = strpos($include, '.')) === false)
			{
				$includes['this'][$include] = $criteria;
			}
			else
			{
				if($criteria === null)
				{
					$includes['forward'][substr($include, 0, $position)][] = substr($include, $position + 1);
				}
				else
				{
					$includes['forward'][substr($include, 0, $position)][substr($include, $position + 1)] = $criteria;
				}
			}
		}

		return $includes;
	}

	/**
	 * Load includes.
	 * 
	 * @access  protected
	 * @param   array      $results  Loaded records
	 */

	protected function loadIncludes(array $results)
	{
		$includes = $this->parseIncludes();

		foreach($includes['this'] as $include => $criteria)
		{
			$forward = isset($includes['forward'][$include]) ? $includes['forward'][$include] : array();

			$results[0]->{$include}()->eagerLoad($results, $include, $criteria, $forward);
		}
	}

	/**
	 * Returns hydrated models.
	 * 
	 * @access  protected
	 * @param   mixed      $results  Database results
	 * @return  array
	 */

	protected function hydrate($results)
	{
		$hydrated = array();

		if(!is_array($results))
		{
			$results = array($results);
		}

		foreach($results as $result)
		{
			$model = $this->model->getClass();

			$hydrated[] = new $model((array) $result, true, false, true);
		}

		if(!empty($hydrated))
		{
			// Eager load related records

			$this->loadIncludes($hydrated);
		}

		return $hydrated;
	}

	/**
	 * Returns a single record from the database.
	 * 
	 * @access  public
	 * @return  \mako\database\ORM
	 */

	public function first()
	{
		$result = $this->query->first();

		if($result !== false)
		{
			$hydrated = $this->hydrate($result);

			return $hydrated[0];
		}

		return false;
	}

	/**
	 * Returns a result set from the database.
	 * 
	 * @access  public
	 * @return  \mako\database\orm\ResultSet
	 */

	public function all()
	{
		$results = $this->query->all();

		if(!empty($results))
		{
			// Hydrate results

			$results = $this->hydrate($results);
		}

		return new ResultSet($results);
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
		$result = call_user_func_array(array($this->query, $name), $arguments);

		if(in_array($name, array('count', 'min', 'max', 'avg', 'column', 'delete', 'update', 'increment', 'decrement')))
		{
			// Return result if the called method is a query builder endpoint

			return $result;
		}
		else
		{
			// Return hydrator instance to allow continued method chaining

			return $this;
		}
	}
}

/** -------------------- End of file --------------------**/