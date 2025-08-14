<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\repositories\group;

use mako\gatekeeper\entities\group\Group;
use mako\gatekeeper\exceptions\GatekeeperException;
use Override;

use function in_array;
use function sprintf;

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
	 */
	protected string $identifier = 'name';

	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $model
	) {
	}

	/**
	 * Returns a model instance.
	 */
	protected function getModel(): Group
	{
		return new $this->model;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function createGroup(array $properties = []): Group
	{
		$group = $this->getModel();

		foreach ($properties as $property => $value) {
			$group->{$property} = $value;
		}

		$group->save();

		return $group;
	}

	/**
	 * Sets the user identifier.
	 */
	public function setIdentifier(string $identifier): void
	{
		if (!in_array($identifier, ['name', 'id'])) {
			throw new GatekeeperException(sprintf('Invalid identifier [ %s ].', $identifier));
		}

		$this->identifier = $identifier;
	}

	/**
	 * Fetches a group by its name.
	 */
	public function getByName(string $name): ?Group
	{
		return $this->getModel()->where('name', '=', $name)->first();
	}

	/**
	 * Fetches a group by its id.
	 */
	public function getById(int $id): ?Group
	{
		return $this->getModel()->where('id', '=', $id)->first();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getByIdentifier(int|string $identifier): ?Group
	{
		return match ($this->identifier) {
			'name'  => $this->getByName($identifier),
			'id'    => $this->getById($identifier),
			default => throw new GatekeeperException(sprintf('Invalid identifier [ %s ].', $identifier)),
 		};
	}
}
