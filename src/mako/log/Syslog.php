<?php

namespace mako\log;

use \mako\Log;

/**
 * Syslog adapter.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Syslog extends \mako\log\Adapter
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
		Log::EMERGENCY => LOG_EMERG,
		Log::ALERT     => LOG_ALERT,
		Log::CRITICAL  => LOG_CRIT,
		Log::ERROR     => LOG_ERR,
		Log::WARNING   => LOG_WARNING,
		Log::NOTICE    => LOG_NOTICE,
		Log::INFO      => LOG_INFO,
		Log::DEBUG     => LOG_DEBUG,
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
		openlog($config['identifier'], LOG_CONS, $config['facility']);
	}
	
	/**
	 * Destructor
	 *
	 * @access public
	 */
	
	public function __destruct()
	{
		closelog();
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
		return syslog($this->types[$type], $message);
	}
}

/** -------------------- End of file -------------------- **/