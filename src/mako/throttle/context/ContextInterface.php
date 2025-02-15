<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\throttle\context;

/**
 * Context interface.
 */
interface ContextInterface
{
	/**
	 * Returns the context identifier.
	 */
	public function getIdentifier(): string;
}
