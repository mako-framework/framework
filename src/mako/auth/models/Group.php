<?php

namespace mako\auth\models;

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
	// Class properties
	//---------------------------------------------

	/**
	 * Table name.
	 * 
	 * @var string
	 */

	protected $tableName = 'groups';

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
		return $this->manyToMany('\mako\auth\models\User');
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