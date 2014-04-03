<?php

namespace mako\http\responses;

use \mako\http\Request;
use \mako\http\Response;

/**
 * Response container interface.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

interface ResponseContainerInterface
{
	public function send(Request $request, Response $response);
}

