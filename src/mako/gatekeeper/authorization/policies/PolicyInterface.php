<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\authorization\policies;

use mako\gatekeeper\entities\user\UserEntityInterface;

/**
 * Policy interface.
 */
interface PolicyInterface
{
	/**
	 * Return a boolean to skip further authorization or null to continue.
	 */
	public function before(?UserEntityInterface $user, string $action, object|string $entity): ?bool;
}
