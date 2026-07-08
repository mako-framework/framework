<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\authorization\policies;

use mako\gatekeeper\entities\user\UserEntityInterface;
use Override;

/**
 * Base policy.
 */
abstract class Policy implements PolicyInterface
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function before(?UserEntityInterface $user, string $action, object|string $entity): ?bool
	{
		return null;
	}
}
