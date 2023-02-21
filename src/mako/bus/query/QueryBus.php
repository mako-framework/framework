<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\bus\query;

use mako\bus\query\exceptions\QueryBusException;
use mako\bus\traits\SingleHandlerTrait;
use mako\syringe\Container;

/**
 * Query bus.
 */
class QueryBus implements QueryBusInterface
{
	use SingleHandlerTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Container $container
	)
	{}

	/**
	 * {@inheritDoc}
	 */
	protected function getUnableToResolveException(object $object): QueryBusException
	{
		return new QueryBusException(vsprintf('No handler has been registered for [ %s ] queries.', [$object::class]));
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(object $query): mixed
	{
		return $this->getHandler($query)($query);
	}
}
