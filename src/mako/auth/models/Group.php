<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\auth\models;

/**
 * Gatekeeper group.
 * 
 * @author  Frederic G. Østby
 */

class Group extends \mako\database\midgard\ORM
{
	use \mako\database\midgard\traits\TimestampedTrait;
	
	/**
	 * Table name.
	 * 
	 * @var string
	 */

	protected $tableName = 'groups';

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
}