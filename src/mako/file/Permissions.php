<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\file;

use InvalidArgumentException;

use function decoct;
use function sprintf;
use function str_pad;
use function strtoupper;

/**
 * File permission collection.
 */
class Permissions
{
	/**
	 * Permissions.
	 *
	 * @var Permission[]
	 */
	protected array $permissions = [];

	/**
	 * Constructor.
	 */
	final public function __construct(Permission ...$permissions)
	{
		$this->permissions = empty($permissions) ? [Permission::NONE] : $permissions;
	}

	/**
	 * Creates a new permission collection from an integer.
	 */
	public static function fromInt(int $permissions): static
	{
		if ($permissions < Permission::NONE->value || $permissions > (Permission::FULL_WITH_ALL_SPECIAL->value)) {
			throw new InvalidArgumentException(sprintf('The integer [ %s ] does not represent a valid octal between 0o0000 and 0o7777.', $permissions));
		}

		if ($permissions === Permission::NONE->value) {
			return new static;
		}

		$basePermissions = [
			Permission::OWNER_READ,
			Permission::OWNER_WRITE,
			Permission::OWNER_EXECUTE,
			Permission::GROUP_READ,
			Permission::GROUP_WRITE,
			Permission::GROUP_EXECUTE,
			Permission::PUBLIC_READ,
			Permission::PUBLIC_WRITE,
			Permission::PUBLIC_EXECUTE,
			Permission::SPECIAL_SETUID,
			Permission::SPECIAL_SETGID,
			Permission::SPECIAL_STICKY,
		];

		$permission = [];

		foreach ($basePermissions as $case) {
			if (($permissions & $case->value) === $case->value) {
				$permission[] = $case;
			}
		}

		return new static(...$permission);
	}

	/**
	 * Add permission.
	 *
	 * @return $this
	 */
	public function add(Permission ...$permissions): static
	{
		$this->permissions = [...$this->permissions, ...$permissions];

		return $this;
	}

	/**
	 * Remove permission.
	 *
	 * @return $this
	 */
	public function remove(Permission ...$permissions): static
	{
		foreach ($permissions as $permission) {
			$this->permissions = array_filter($this->permissions, fn ($value) => $value !== $permission);
		}

		return $this;
	}

	/**
	 * Returns the permissions.
	 *
	 * @return Permission[]
	 */
	public function getPermissions(): array
	{
		return static::fromInt($this->toInt())->permissions;
	}

	/**
	 * Returns TRUE if the permissions contain the specified permissions and FALSE if not.
	 */
	public function hasPermissions(Permission ...$permissions): bool
	{
		return Permission::hasPermissions(Permission::calculate(...$this->permissions), ...$permissions);
	}

	/**
	 * Returns an integer representation of the permissions.
	 */
	public function toInt(): int
	{
		return Permission::calculate(...$this->permissions);
	}

	/**
	 * Returns an octal string representation of the permissions.
	 */
	public function toOctalString(): string
	{
		return str_pad(decoct(Permission::calculate(...$this->permissions)), 3, '0', STR_PAD_LEFT);
	}

	/**
	 * Returns a rwx string representation of a permission group.
	 */
	protected function getGroupAsRwxString(int $permissions, Permission $read, Permission $write, Permission $execute, string $group): string
	{
		$rwx = '';

		// Read & Write

		$rwx .= ($permissions & $read->value) ? 'r' : '-';
		$rwx .= ($permissions & $write->value) ? 'w' : '-';

		// Determine special bit

		$specialChar = match ($group) {
			'owner' => ($permissions & Permission::SPECIAL_SETUID->value) ? 's' : null,
			'group' => ($permissions & Permission::SPECIAL_SETGID->value) ? 's' : null,
			'public' => ($permissions & Permission::SPECIAL_STICKY->value) ? 't' : null,
		};

		// Execute + special bit handling

		$hasExecute = ($permissions & $execute->value) !== 0;

		$rwx .= $specialChar !== null ? ($hasExecute ? $specialChar : strtoupper($specialChar)) : ($hasExecute ? 'x' : '-');

		// Return rwx string

		return $rwx;
	}

	/**
	 * Returns a rwx string representation of the permissions.
	 */
	public function toRwxString(): string
	{
		$permissions = Permission::calculate(...$this->permissions);

		$owner = $this->getGroupAsRwxString($permissions, Permission::OWNER_READ, Permission::OWNER_WRITE, Permission::OWNER_EXECUTE, 'owner');
		$group = $this->getGroupAsRwxString($permissions, Permission::GROUP_READ, Permission::GROUP_WRITE, Permission::GROUP_EXECUTE, 'group');
		$public = $this->getGroupAsRwxString($permissions, Permission::PUBLIC_READ, Permission::PUBLIC_WRITE, Permission::PUBLIC_EXECUTE, 'public');

		return "{$owner}{$group}{$public}";
	}
}
