<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core\error\handlers;

use \Exception;
use \Psr\Log\LoggerInterface;

/**
 * Store interface.
 *
 * @author  Frederic G. Østby
 */

interface HandlerInterface
{
	public function __construct(Exception $exception);
	public function __destruct();
	public function setLogger(LoggerInterface $logger);
	public function handle($showDetails = true);
}

