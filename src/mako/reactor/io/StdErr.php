<?php

namespace mako\reactor\io;

/**
 * Stderr.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class StdErr extends \mako\reactor\io\StreamOutput
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 */

	public function __construct()
	{
		parent::__construct(fopen('php://stderr', 'w'));
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	// Nothing here
}

