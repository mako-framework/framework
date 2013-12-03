<?php

namespace mako\logging\adapters;

use \mako\logging\Log;
use \Exception;

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

	abstract protected function writeLog($message, $type);

	/**
	 * Write a message to the log.
	 * 
	 * @access  public
	 * @param   string|\Exception  $message  Message to log
	 * @param   int                $type     (optional) Log type
	 * @return  boolean
	 */

	public function write($message, $type = Log::ERROR)
	{
		if($message instanceof Exception)
		{
			$message = vsprintf('%s: %s in %s at line %s' . PHP_EOL . '%s', 
			[
				get_class($message),
				$message->getMessage(),
				$message->getFile(),
				$message->getLine(),
				$message->getTraceAsString(),
			]);
		}

		return $this->writeLog($message, $type);
	}

	/**
	 * Write a emergency message to the log.
	 * 
	 * @access  public
	 * @param   string|\Exception  $message  Message to log
	 * @return  boolean
	 */

	public function emergency($message)
	{
		return $this->write($message, Log::EMERGENCY);
	}

	/**
	 * Write an alert message to the log.
	 * 
	 * @access  public
	 * @param   string|\Exception  $message  Message to log
	 * @return  boolean
	 */

	public function alert($message)
	{
		return $this->write($message, Log::ALERT);
	}

	/**
	 * Write a critical message to the log.
	 * 
	 * @access  public
	 * @param   string|\Exception  $message  Message to log
	 * @return  boolean
	 */

	public function critical($message)
	{
		return $this->write($message, Log::CRITICAL);
	}

	/**
	 * Write an error message to the log.
	 * 
	 * @access  public
	 * @param   string|\Exception  $message  Message to log
	 * @return  boolean
	 */

	public function error($message)
	{
		return $this->write($message, Log::ERROR);
	}

	/**
	 * Write a warning message to the log.
	 * 
	 * @access  public
	 * @param   string|\Exception  $message  Message to log
	 * @return  boolean
	 */

	public function warning($message)
	{
		return $this->write($message, Log::WARNING);
	}

	/**
	 * Write a notice message to the log.
	 * 
	 * @access  public
	 * @param   string|\Exception  $message  Message to log
	 * @return  boolean
	 */

	public function notice($message)
	{
		return $this->write($message, Log::NOTICE);
	}

	/**
	 * Write an info message to the log.
	 * 
	 * @access  public
	 * @param   string|\Exception  $message  Message to log
	 * @return  boolean
	 */

	public function info($message)
	{
		return $this->write($message, Log::INFO);
	}

	/**
	 * Write a debug message to the log.
	 * 
	 * @access  public
	 * @param   string|\Exception  $message  Message to log
	 * @return  boolean
	 */

	public function debug($message)
	{
		return $this->write($message, Log::DEBUG);
	}
}

/** -------------------- End of file -------------------- **/