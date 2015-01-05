<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query;

use mako\database\query\Query;

/**
 * Subquery container.
 *
 * @author  Frederic G. Østby
 */

class Subquery
{
	/**
	 * Query builder.
	 *
	 * @var \mako\database\query\Query
	 */

	protected $query;

	/**
	 * Alias.
	 *
	 * @var string
	 */

	protected $alias = null;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\database\query\Query  $query  Query builder
	 * @param   string                      $alias  Subquery alias
	 */

	public function __construct(Query $query, $alias = null)
	{
		$this->query = $query;
		$this->alias = $alias;
	}

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