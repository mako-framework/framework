<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\exceptions;

/**
 * Provides headers interface.
 */
interface ProvidesHeadersInterface
{
	/**
	 * Returns the headers.
	 */
	public function getHeaders(): array;
}
