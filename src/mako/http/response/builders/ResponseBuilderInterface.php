<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response\builders;

use mako\http\Request;
use mako\http\Response;

/**
 * Response builder interface.
 */
interface ResponseBuilderInterface
{
	/**
	 * Builds the response.
	 */
	public function build(Request $request, Response $response): void;
}
