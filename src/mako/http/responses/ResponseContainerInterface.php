<?php

namespace mako\http\responses;

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
	public function send(Response $response);
}

/** -------------------- End of file -------------------- **/