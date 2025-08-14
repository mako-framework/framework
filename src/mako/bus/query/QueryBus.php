<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\bus\query;

use mako\bus\query\exceptions\QueryBusException;
use mako\bus\traits\SingleHandlerTrait;
use mako\syringe\Container;
use Override;

use function sprintf;

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
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function getUnableToResolveException(object $object): QueryBusException
	{
		return new QueryBusException(sprintf('No handler has been registered for [ %s ] queries.', $object::class));
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function handle(object $query): mixed
	{
		return $this->getHandler($query)($query);
	}
}
