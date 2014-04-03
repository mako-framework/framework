<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\responses;

use \mako\http\Request;
use \mako\http\Response;

/**
 * Response container interface.
 *
 * @author  Frederic G. Østby
 */

interface ResponseContainerInterface
{
	public function send(Request $request, Response $response);
}

