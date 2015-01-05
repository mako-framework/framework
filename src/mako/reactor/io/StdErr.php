<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\reactor\io;

use mako\reactor\io\StreamOutput;

/**
 * Stderr.
 *
 * @author  Frederic G. Østby
 */

class StdErr extends StreamOutput
{
	/**
	 * Constructor.
	 * 
	 * @access  public
	 */

	public function __construct()
	{
		parent::__construct(fopen('php://stderr', 'w'));
	}
}