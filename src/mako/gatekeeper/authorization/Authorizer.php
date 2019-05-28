<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\authorization;

use mako\gatekeeper\authorization\policies\PolicyInterface;
use mako\gatekeeper\entities\user\UserEntityInterface;
use mako\syringe\Container;

use function get_class;
use function is_object;
use function vsprintf;

/**
 * Authorizer.
 *
 * @author Frederic G. Østby
 */
class Authorizer implements AuthorizerInterface
{
	/**
	 * Container.
	 *
	 * @var \mako\syringe\Container
	 */
	protected $container;

	/**
	 * Policies.
	 *
	 * @var array
	 */
	protected $policies = [];

	/**
	 * Constructor.
	 *
	 * @param \mako\syringe\Container|null $container Container
	 */
	public function __construct(?Container $container = null)
	{
		$this->container = $container ?? new Container;
	}

	/**
	 * {@inheritdoc}
	 */
	public function registerPolicy(string $entityClass, string $policyClass): void
	{
		$this->policies[$entityClass] = $policyClass;
	}

	/**
	 * Policy factory.
	 *
	 * @param  object|string                                           $entity Entity instance or class name
	 * @throws \mako\gatekeeper\authorization\AuthorizerException
	 * @return \mako\gatekeeper\authorization\policies\PolicyInterface
	 */
	protected function policyFactory($entity): PolicyInterface
	{
		$entityClass = is_object($entity) ? get_class($entity) : $entity;

		if(!isset($this->policies[$entityClass]))
		{
			throw new AuthorizerException(vsprintf('There is no authorization policy registered for [ %s ] entities.', [$entityClass]));
		}

		return $this->container->get($this->policies[$entityClass]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function can(?UserEntityInterface $user, string $action, $entity, ...$parameters): bool
	{
		$policy = $this->policyFactory($entity);

		if(($isAuthorized = $policy->before($user, $action, $entity, ...$parameters)) !== null)
		{
			return $isAuthorized;
		}

		return $policy->$action($user, $entity, ...$parameters);
	}
}
