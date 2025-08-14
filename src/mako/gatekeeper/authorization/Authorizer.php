<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\authorization;

use mako\gatekeeper\authorization\exceptions\AuthorizerException;
use mako\gatekeeper\authorization\policies\PolicyInterface;
use mako\gatekeeper\entities\user\UserEntityInterface;
use mako\syringe\Container;
use Override;

use function is_object;
use function sprintf;

/**
 * Authorizer.
 */
class Authorizer implements AuthorizerInterface
{
	/**
	 * Policies.
	 */
	protected array $policies = [];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Container $container = new Container
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function registerPolicy(string $entityClass, string $policyClass): void
	{
		$this->policies[$entityClass] = $policyClass;
	}

	/**
	 * Policy factory.
	 */
	protected function policyFactory(object|string $entity): PolicyInterface
	{
		$entityClass = is_object($entity) ? $entity::class : $entity;

		if (!isset($this->policies[$entityClass])) {
			throw new AuthorizerException(sprintf('There is no authorization policy registered for [ %s ] entities.', $entityClass));
		}

		return $this->container->get($this->policies[$entityClass]);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function can(?UserEntityInterface $user, string $action, $entity, ...$parameters): bool
	{
		$policy = $this->policyFactory($entity);

		if (($isAuthorized = $policy->before($user, $action, $entity)) !== null) {
			return $isAuthorized;
		}

		return $policy->{$action}($user, $entity, ...$parameters);
	}
}
