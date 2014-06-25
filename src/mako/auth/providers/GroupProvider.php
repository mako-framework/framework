<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\auth\providers;

/**
 * Group provider.
 *
 * @author  Frederic G. Østby
 */

class GroupProvider implements \mako\auth\providers\GroupProviderInterface
{
	/**
	 * Model.
	 * 
	 * @var string
	 */

	protected $model;

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $model  Model class
	 */

	public function __construct($model)
	{
		$this->model = $model;
	}

	/**
	 * Creates and returns a group.
	 * 
	 * @access  public
	 * @param   string                           $name  Group name
	 * @return  \mako\auth\group\GroupInterface
	 */

	public function createGroup($name)
	{
		$model = $this->model;

		$group = new $model;

		$group->setName($name);

		$group->save();

		return $group;
	}

	/**
	 * Fetches a group by its name.
	 * 
	 * @access  public
	 * @param   string                                   $name  Group name
	 * @return  \mako\auth\group\GroupInterface|boolean
	 */

	public function getByName($name)
	{
		$model = $this->model;

		return $model::where('name', '=', $name)->first();
	}

	/**
	 * Fetches a group by its id.
	 * 
	 * @access  public
	 * @param   int                                      $id  Group id
	 * @return  \mako\auth\group\GroupInterface|boolean
	 */

	public function getById($id)
	{
		$model = $this->model;

		return $model::where('id', '=', $id)->first();
	}
}