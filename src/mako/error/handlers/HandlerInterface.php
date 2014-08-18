<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\error\handlers;

use \Exception;

/**
 * Store interface.
 *
 * @author  Frederic G. Østby
 */

interface HandlerInterface
{
	public function __construct(Exception $exception);
	public function handle($showDetails = true);
}