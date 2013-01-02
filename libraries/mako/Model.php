<?php

namespace mako;

use \mako\Database;

/**
 * Base model.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

abstract class Model
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Database connection object.
	 *
	 * @var mako\database\Connection
	 */

	protected $connection;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   string  $connection  (optional) Name of the database connection to use (as defined in the config)
	 */

	public function __construct($connection = null)
	{
		$this->connection = Database::connection($connection);
	}
	
	/**
	 * Factory method making method chaining possible right off the bat.
	 *
	 * @access  public
	 * @param   string      $connection  (optional) Name of the database connection to use (as defined in the config)
	 * @return  mako\Model
	 */
	
	public static function factory($connection = null)
	{
		return new static($connection);
	}
}

/** -------------------- End of file --------------------**/