<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query;

/**
 * Raw SQL container.
 *
 * @author  Frederic G. Østby
 */

class Raw
{
	/**
	 * Raw SQL
	 *
	 * @var string
	 */

	protected $sql;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   string  $sql  Raw SQL
	 */

	public function __construct($sql)
	{
		$this->sql = $sql;
	}

	/**
	 * Returns the raw SQL.
	 *
	 * @access  public
	 * @return  string
	 */

	public function get()
	{
		return $this->sql;
	}
}