<?php

namespace mako\database\query;

use \mako\database\Query;

/**
 * Subquery container.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Subquery
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Query builder.
	 *
	 * @var \mako\database\Query
	 */

	protected $query;

	/**
	 * Alias.
	 *
	 * @var string
	 */

	protected $alias = null;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\database\Query  $query  Query builder
	 * @param   string                $alias  Subquery alias
	 */

	public function __construct(Query $query, $alias = null)
	{
		$this->query = $query;
		$this->alias = $alias;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns the compiled query.
	 *
	 * @access  public
	 * @return  array
	 */

	public function get()
	{
		$query = $this->query->getCompiler()->select();

		$query['sql'] = '(' . $query['sql'] . ')';

		if($this->alias !== null)
		{
			$query['sql'] .= ' AS ' . $this->query->getCompiler()->wrap($this->alias);
		}

		return $query;
	}
}

/** -------------------- End of file -------------------- **/