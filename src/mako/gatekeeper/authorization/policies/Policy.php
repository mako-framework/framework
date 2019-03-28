<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\authorization\policies;

use mako\gatekeeper\entities\user\UserEntityInterface;

/**
 * Base policy.
 *
 * @author Frederic G. Østby
 */
abstract class Policy implements PolicyInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function before(?UserEntityInterface $user, string $action, $entity): ?bool
	{
		return null;
	}
}
