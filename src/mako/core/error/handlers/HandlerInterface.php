<?php

namespace mako\core\error\handlers;

use \Exception;

/**
 * Store interface.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

interface HandlerInterface
{
	public function __construct(Exception $exception);
	public function handle($showDetails = true);
}

/** -------------------- End of file -------------------- **/