<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers;

/**
 * Provides exception id interface.
 */
interface ProvidesExceptionIdInterface
{
	/**
	 * Returns the exception id.
	 *
	 * @return string
	 */
	public function getExceptionId(): string;
}
