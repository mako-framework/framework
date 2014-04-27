<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\midgard;

/**
 * Hydrator.
 *
 * @author  Frederic G. Østby
 */

use \mako\database\Connection;
use \mako\database\midgard\ORM;
use \mako\database\midgard\ResultSet;
use \mako\database\midgard\ReadOnlyRecordException;
use \mako\pagination\Pagination;
use \BadMethodCallException;

class Hydrator extends \mako\database\query\Query
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Instance of the model to hydrate.
	 * 
	 * @var \mako\database\midgard\ORM
	 */

	protected $model;

	/**
	 * Should hydrated models be made read only?
	 * 
	 * @var boolean
	 */

	protected $makeReadOnly = false;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\database\Connection   $connection  Database connection
	 * @param   \mako\database\midgard\ORM  $model       Model to hydrate
	 */

	public function __construct(Connection $connection, ORM $model)
	{
		$this->model = $model;

		$this->makeReadOnly = $this->model->isReadOnly();

		parent::__construct($connection, $this->model->getTable());
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Adds a JOIN clause.
	 *
	 * @access  public
	 * @param   string                      $table     Table name
	 * @param   string|\Closure             $column1   (optional) Column name or closure
	 * @param   string                      $operator  (optional) Operator
	 * @param   string                      $column2   (optional) Column name
	 * @param   string                      $type      (optional) Join type
	 * @return  \mako\database\query\Query
	 */

	public function join($table, $column1 = null, $operator = null, $column2 = null, $type = 'INNER')
	{
		if(empty($this->joins))
		{
			$this->columns = [$this->model->getTable() . '.*'];
		}

		return parent::join($table, $column1, $operator, $column2, $type);
	}

	/**
	 * Inserts data into the chosen table.
	 *
	 * @access  public
	 * @param   array    $values  Associative array of column values
	 * @return  boolean
	 */

	public function insert(array $values)
	{
		if($this->model->isReadOnly())
		{
			throw new ReadOnlyRecordException(vsprintf("%s(): Attempted to crate a read-only record.", [__METHOD__]));
		}

		return parent::insert($values);
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
		if($this->model->isReadOnly())
		{
			throw new ReadOnlyRecordException(vsprintf("%s(): Attempted to update a read-only record.", [__METHOD__]));
		}

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
		if($this->model->isReadOnly())
		{
			throw new ReadOnlyRecordException(vsprintf("%s(): Attempted to delete a read-only record.", [__METHOD__]));
		}

		return parent::delete();
	}

	/**
	 * Returns a record using the value of its primary key.
	 * 
	 * @access  public
	 * @param   int                         $id       Primary key
	 * @param   array                       $columns  (optional) Columns to select
	 * @return  \mako\database\midgard\ORM
	 */

	public function get($id, array $columns = [])
	{
		return $this->where($this->model->getPrimaryKey(), '=', $id)->first($columns);
	}

	/**
	 * Sets the relations to eager load.
	 * 
	 * @access  public
	 * @param   string|array                     $includes  Relation or array of relations to eager load
	 * @return  \mako\database\midgard\Hydrator
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
	 * @param   string|array                     $excludes  Relation or array of relations to exclude from eager loading
	 * @return  \mako\database\midgard\Hydrator
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
		$includes = ['this' => [], 'forward' => []];

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
			$forward = isset($includes['forward'][$include]) ? $includes['forward'][$include] : [];

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
		$hydrated = [];

		if(!is_array($results))
		{
			$results = [$results];
		}

		foreach($results as $result)
		{
			$model = $this->model->getClass();

			$hydrated[] = new $model((array) $result, true, false, true, $this->makeReadOnly);
		}

		if(!empty($hydrated))
		{
			$this->loadIncludes($hydrated);
		}

		return $hydrated;
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
		if(!empty($columns))
		{
			$this->makeReadOnly = true;
		}

		$result = parent::first($columns ?: $this->columns);

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
	 * @param   array                             $columns  (optional) Columns to select
	 * @return  \mako\database\midgard\ResultSet
	 */

	public function all(array $columns = [])
	{
		if(!empty($columns))
		{
			$this->makeReadOnly = true;
		}

		$results = parent::all($columns ?: $this->columns);

		if(!empty($results))
		{
			$results = $this->hydrate($results);
		}

		return new ResultSet($results);
	}
	
    /**
     * Parse all pagination parameters using Pagination Object
     *
     * @access  public
     * @param   \mako\pagination\Pagination      $pagination  Pagination Object
     * @return  \mako\database\midgard\Hydrator
     */

    public function paginate(Pagination $pagination)
    {
        $this->limit = $pagination->limit();

        $this->offset = $pagination->offset();

        return $this;
    }

	/**
	 * Magic method that allows us to call model scopes.
	 * 
	 * @access  public
	 * @param   string                           $name       Method name
	 * @param   array                            $arguments  Method arguments
	 * @return  \mako\database\midgard\Hydrator
	 */

	public function __call($name, $arguments)
	{
		if(!method_exists($this->model, 'scope_' . $name))
		{
			throw new BadMethodCallException(vsprintf("Call to undefined method %s::%s().", [__CLASS__, $name]));
		}

		array_unshift($arguments, $this);

		call_user_func_array([$this->model, 'scope_' . $name], $arguments);

		return $this;
	}
}
