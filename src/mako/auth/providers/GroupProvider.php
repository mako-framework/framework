<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\auth\providers;

use mako\auth\providers\GroupProviderInterface;

/**
 * Group provider.
 *
 * @author  Frederic G. Østby
 */

class GroupProvider implements GroupProviderInterface
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */

	public function getByName($name)
	{
		$model = $this->model;

		return $model::where('name', '=', $name)->first();
	}

	/**
	 * {@inheritdoc}
	 */

	public function getById($id)
	{
		$model = $this->model;

		return $model::where('id', '=', $id)->first();
	}
}