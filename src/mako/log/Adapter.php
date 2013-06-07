<?php

namespace mako\log;

use \mako\Log;

/**
 * Log adapter.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

abstract class Adapter
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	abstract public function __construct(array $config);

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	abstract public function write($message, $type = Log::ERROR);
}

/** -------------------- End of file -------------------- **/