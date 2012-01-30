<?php

namespace mako\log
{
	use \mako\Log;
	
	/**
	* File adapter.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2012 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/
	
	class File extends \mako\log\Adapter
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------
		
		/**
		* Path to the logs.
		*/
		
		protected $path;
		
		/**
		* Log types.
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
		* @param   array   Configuration
		*/
		
		public function __construct(array $config)
		{
			$this->path = $config['path'];
		}
		
		//---------------------------------------------
		// Class methods
		//---------------------------------------------
		
		/**
		* Writes message to log.
		*
		* @access  public
		* @param   string   The message to write to the log
		* @param   int      (optional) Message type
		* @return  boolean  TRUE on success or FALSE on failure.
		*/
		
		public function write($message, $type = Log::ERROR)
		{
			$file = rtrim($this->path, '/') . '/' . $this->types[$type] . '_' . gmdate('Y_m_d') . '.log';
			
			$message = '[' . gmdate('d-M-Y H:i:s') . '] ' . $message . PHP_EOL;
			
			return (bool) file_put_contents($file, $message, FILE_APPEND);
		}
	}
}

/** -------------------- End of file --------------------**/