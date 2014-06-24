<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\auth\models;


/**
 * Gatekeeper user.
 * 
 * @author  Frederic G. Østby
 */

class User extends \mako\database\midgard\ORM
{
	use \mako\database\midgard\traits\TimestampedTrait;
	/**
	 * Provide additional common methods.
	 *
	 */

	use \mako\auth\traits\UserTrait;

	/**
	 * Table name.
	 * 
	 * @var string
	 */

	protected $tableName = 'users';

	/**
	 * User permissions.
	 * 
	 * @var array
	 */

	protected $permissions = [];

	/**
	 * Many to many relation to the groups table.
	 * 
	 * @access  public
	 * @return  \mako\database\midgard\relation\ManyToMany
	 */

	public function groups()
	{
		return $this->manyToMany('\mako\auth\models\Group');
	}
}