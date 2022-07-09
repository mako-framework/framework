<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\repositories\group;

use mako\gatekeeper\entities\group\Group;
use mako\gatekeeper\exceptions\GatekeeperException;

use function in_array;
use function vsprintf;

/**
 * Group repository.
 *
 * @method \mako\gatekeeper\entities\group\Group      createGroup(array $properties = [])
 * @method \mako\gatekeeper\entities\group\Group|null getByIdentifier($identifier)
 */
class GroupRepository implements GroupRepositoryInterface
{
	/**
	 * Group identifier.
	 *
	 * @var string
	 */
	protected $identifier = 'name';

	/**
	 * Constructor.
	 *
	 * @param string $model Model name
	 */
	public function __construct(
		protected string $model
	)
	{}

	/**
	 * Returns a model instance.
	 *
	 * @return \mako\gatekeeper\entities\group\Group
	 */
	protected function getModel(): Group
	{
		return new $this->model;
	}

	/**
	 * {@inheritDoc}
	 */
	public function createGroup(array $properties = []): Group
	{
		$group = $this->getModel();

		foreach($properties as $property => $value)
		{
			$group->$property = $value;
		}

		$group->save();

		return $group;
	}

	/**
	 * Sets the user identifier.
	 *
	 * @param string $identifier User identifier
	 */
	public function setIdentifier(string $identifier): void
	{
		if(!in_array($identifier, ['name', 'id']))
		{
			throw new GatekeeperException(vsprintf('Invalid identifier [ %s ].', [$identifier]));
		}

		$this->identifier = $identifier;
	}

	/**
	 * Fetches a group by its name.
	 *
	 * @param  string                                     $name Group name
	 * @return \mako\gatekeeper\entities\group\Group|null
	 */
	public function getByName(string $name): ?Group
	{
		return $this->getModel()->where('name', '=', $name)->first();
	}

	/**
	 * Fetches a group by its id.
	 *
	 * @param  int                                        $id Group id
	 * @return \mako\gatekeeper\entities\group\Group|null
	 */
	public function getById(int $id): ?Group
	{
		return $this->getModel()->where('id', '=', $id)->first();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return \mako\gatekeeper\entities\group\Group|null
	 */
	public function getByIdentifier(int|string $identifier): ?Group
	{
		return match($this->identifier)
		{
			'name'  => $this->getByName($identifier),
			'id'    => $this->getById($identifier),
			default => throw new GatekeeperException(vsprintf('Invalid identifier [ %s ].', [$identifier])),
 		};
	}
}
