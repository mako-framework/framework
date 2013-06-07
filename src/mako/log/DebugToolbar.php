<?php

namespace mako\log;

use \mako\Log;
use \mako\DebugToolbar as Toolbar;

/**
 * Debug toolbar adapter.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class DebugToolbar extends \mako\log\Adapter
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------
	
	/**
	 * Log types.
	 *
	 * @var array
	 */
	
	protected $types = array
	(
		Log::EMERGENCY => 'emergency',
		Log::ALERT     => 'alert',
		Log::CRITICAL  => 'critical',
		Log::ERROR     => 'error',
		Log::WARNING   => 'warning',
		Log::NOTICE    => 'notice',
		Log::INFO      => 'info',
		Log::DEBUG     => 'debug',
	);
	
	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------
	
	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   array   $config  Configuration
	 */
	
	public function __construct(array $config)
	{
		
	}
	
	//---------------------------------------------
	// Class methods
	//---------------------------------------------
	
	/**
	 * Writes message to log.
	 *
	 * @access  public
	 * @param   string   $message  The message to write to the log
	 * @param   int      $type     (optional) Message type
	 * @return  boolean
	 */
	
	public function write($message, $type = Log::ERROR)
	{
		Toolbar::log($message, $this->types[$type]);

		return true;
	}
}

/** -------------------- End of file -------------------- **/