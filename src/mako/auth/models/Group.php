<?php

namespace app\lib\auth;

use \mako\core\Config;

/**
 * Gatekeeper group.
 * 
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Group extends \mako\database\midgard\ORM
{
	//---------------------------------------------
	// Relations
	//---------------------------------------------

	/**
	 * Many to many relation to the users table.
	 * 
	 * @access  public
	 * @return  \mako\database\midgard\relation\ManyToMany
	 */

	public function users()
	{
		return $this->manyToMany(Config::get('gatekeeper.user_model'));
	}

	//---------------------------------------------
	// Getters and setters
	//---------------------------------------------

	/**
	 * Persmission setter.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function set_permissions($permissions)
	{
		return json_encode($permissions);
	}

	/**
	 * Permission getter.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function get_permissions($permissions)
	{
		return json_decode($permissions, true);
	}
}

/** -------------------- End of file --------------------**/