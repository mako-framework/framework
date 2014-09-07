<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\routing\filters;

/**
 * Filter interface.
 * 
 * @author  Frederic G. Østby
 */

interface FilterInterface
{
	/**
	 * Filter.
	 * 
	 * @access  public
	 * @return  mixed
	 */

	public function filter();
}