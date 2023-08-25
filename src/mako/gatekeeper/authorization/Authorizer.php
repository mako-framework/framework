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

use function is_object;
use function vsprintf;

/**
 * Authorizer.
 */
class Authorizer implements AuthorizerInterface
{
	/**
	 * Policies.
	 *
	 * @var array
	 */
	protected $policies = [];

	/**
	 * Constructor.
	 *
	 * @param \mako\syringe\Container $container Container
	 */
	public function __construct(
		protected Container $container = new Container
	)
	{}

	/**
	 * {@inheritDoc}
	 */
	public function registerPolicy(string $entityClass, string $policyClass): void
	{
		$this->policies[$entityClass] = $policyClass;
	}

	/**
	 * Policy factory.
	 *
	 * @param  object|string                                           $entity Entity instance or class name
	 * @return \mako\gatekeeper\authorization\policies\PolicyInterface
	 */
	protected function policyFactory(object|string $entity): PolicyInterface
	{
		$entityClass = is_object($entity) ? $entity::class : $entity;

		if(!isset($this->policies[$entityClass]))
		{
			throw new AuthorizerException(vsprintf('There is no authorization policy registered for [ %s ] entities.', [$entityClass]));
		}

		return $this->container->get($this->policies[$entityClass]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function can(?UserEntityInterface $user, string $action, $entity, ...$parameters): bool
	{
		$policy = $this->policyFactory($entity);

		if(($isAuthorized = $policy->before($user, $action, $entity)) !== null)
		{
			return $isAuthorized;
		}

		return $policy->$action($user, $entity, ...$parameters);
	}
}
