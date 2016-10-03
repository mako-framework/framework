<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query;

use Closure;

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
	 * @param   \Closure|\mako\database\query\Query  $query  Query builder
	 * @param   string                               $alias  Subquery alias
	 */
	public function __construct($query, string $alias = null)
	{
		$this->query = $query;
		$this->alias = $alias;
	}

	/**
	 * Converts a subquery closure to query a builder instance.
	 *
	 * @access  public
	 * @param   \mako\database\query\Query     $query  Query builder instance
	 * @return  \mako\database\query\Subquery
	 */
	public function build(Query $query): Subquery
	{
		if($this->query instanceof Closure)
		{
			$subquery = $this->query;

			$this->query = $query->newInstance();

			$subquery($this->query);
		}

		return $this;
	}

	/**
	 * Returns the compiled query.
	 *
	 * @access  public
	 * @return  array
	 */
	public function get(): array
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