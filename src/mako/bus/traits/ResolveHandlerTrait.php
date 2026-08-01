<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\bus\traits;

use mako\syringe\Container;

use function is_callable;
use function is_string;

/**
 * Resolve handler trait.
 *
 * @property Container $container
 */
trait ResolveHandlerTrait
{
	/**
	 * Resolves the handler.
	 */
	protected function resolveHandler(callable|string $handler): callable
	{
		if (is_string($handler)) {
			if (is_callable($handler)) {
				return $handler;
			}

			return $this->container->get($handler);
		}

		return $handler;
	}
}
