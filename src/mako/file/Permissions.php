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
		if ($permissions < Permission::NONE->value || $permissions > Permission::FULL->value) {
			throw new InvalidArgumentException(sprintf('The integer [ %s ] does not represent a valid octal between 0o000 and 0o777.', $permissions));
		}

		if ($permissions === Permission::NONE->value) {
			return new static;
		}

		$permission = [];

		foreach (Permission::cases() as $case) {
			if ($case === Permission::NONE
				|| $case === Permission::OWNER_FULL
				|| $case === Permission::GROUP_FULL
				|| $case === Permission::PUBLIC_FULL
				|| $case === Permission::FULL) {
				continue;
			}

			if (($permissions & $case->value) === $case->value) {
				$permission[] = $case;
			}
		}

		return new static(...$permission);
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
	protected function getGroupAsRwxString(int $permissions, Permission $read, Permission $write, Permission $execute): string
	{
		$rwx = '';

		$rwx .= ($permissions & $read->value) ? 'r' : '-';
		$rwx .= ($permissions & $write->value) ? 'w' : '-';
		$rwx .= ($permissions & $execute->value) ? 'x' : '-';

		return $rwx;
	}

	/**
	 * Returns a rwx string representation of the permissions.
	 */
	public function toRwxString(): string
	{
		$permissions = Permission::calculate(...$this->permissions);

		$owner = $this->getGroupAsRwxString($permissions, Permission::OWNER_READ, Permission::OWNER_WRITE, Permission::OWNER_EXECUTE);
		$group = $this->getGroupAsRwxString($permissions, Permission::GROUP_READ, Permission::GROUP_WRITE, Permission::GROUP_EXECUTE);
		$public = $this->getGroupAsRwxString($permissions, Permission::PUBLIC_READ, Permission::PUBLIC_WRITE, Permission::PUBLIC_EXECUTE);

		return "{$owner}{$group}{$public}";
	}
}
