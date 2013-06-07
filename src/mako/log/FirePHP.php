<?php

namespace mako\log;

use \mako\Log;

/**
 * FirePHP adapter.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class FirePHP extends \mako\log\Adapter
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
		Log::EMERGENCY => 'ERROR',
		Log::ALERT     => 'ERROR',
		Log::CRITICAL  => 'ERROR',
		Log::ERROR     => 'ERROR',
		Log::WARNING   => 'WARN',
		Log::NOTICE    => 'INFO',
		Log::INFO      => 'INFO',
		Log::DEBUG     => 'LOG',
	);
	
	/**
	 * Counter.
	 *
	 * @var int
	 */
	
	protected $counter = 0;
	
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
		if(!headers_sent())
		{
			header('X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2');
			header('X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3');
			header('X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1');
		}
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
		if(!headers_sent())
		{				
			$content = json_encode(array(array('Type' => $this->types[$type]), $message));
							
			header('X-Wf-1-1-1-' . ++$this->counter . ': ' . strlen($content) . '|' . $content . '|');
						
			return true;
		}
		
		return false;
	}
}

/** -------------------- End of file -------------------- **/