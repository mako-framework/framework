<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\routing;

use RuntimeException;

/**
 * Filter collection.
 *
 * @author  Frederic G. Østby
 */

class Filters
{
	/**
	 * Registered filters.
	 *
	 * @var array
	 */

	protected $filters = [];

	/**
	 * Adds a filter.
	 *
	 * @access  public
	 * @param   string           $name    Filter name
	 * @param   string|\Closure  $filter  Filter class or closure
	 */

	public function register($name, $filter)
	{
		$this->filters[$name] = $filter;
	}

	/**
	 * Returns the chosen filter.
	 *
	 * @access  public
	 * @param   string           $filter  Filter name
	 * @return  string|\Closure
	 */

	public function get($filter)
	{
		if(!isset($this->filters[$filter]))
		{
			throw new RuntimeException(vsprintf("%s(): No filter named [ %s ] has been defined.", [__METHOD__, $filter]));
		}

		return $this->filters[$filter];
	}
}