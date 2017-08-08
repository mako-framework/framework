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
 *
 * @author Frederic G. Østby
 */
interface ResponseBuilderInterface
{
	/**
	 * Builds the response.
	 *
	 * @param \mako\http\Request  $request  Request instance
	 * @param \mako\http\Response $response Response instance
	 */
	public function build(Request $request, Response $response);
}
