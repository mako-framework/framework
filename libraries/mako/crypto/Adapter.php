<?php

namespace mako\crypto;

/**
* Crypto adapter.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
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

	abstract public function encrypt($string);

	abstract public function decrypt($string);
}

/** -------------------- End of file --------------------**/