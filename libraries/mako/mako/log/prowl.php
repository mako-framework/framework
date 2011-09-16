<?php

namespace mako\log
{
	use \Mako;
	use \mako\Log;
	use \mako\Prowl as Grrr;
	
	/**
	* Prowl adapter.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/
	
	class Prowl extends \mako\log\Adapter
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------
		
		/**
		*Growl server hostname or IP.
		*/
		
		protected $configuration;
		
		/**
		* Growl server password.
		*/
		
		/**
		* Log types.
		*/
		
		protected $types = array
		(
			Log::EMERGENCY => 'Emergency',
			Log::ALERT     => 'Alert',
			Log::CRITICAL  => 'Critical',
			Log::ERROR     => 'Error',
			Log::WARNING   => 'Warning',
			Log::NOTICE    => 'Notice',
			Log::INFO      => 'Info',
			Log::DEBUG     => 'Debug',
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
			$this->configuration = $config['configuration'];
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
			$priorities = array_combine(array_keys($this->types), array(2, 1, 2, 1, 1, 0, 0, 0));
			
			Grrr::factory($this->configuration)->notify($this->types[$type], $message, null, $priorities[$type]);					
		}
	}
}

/** -------------------- End of file --------------------**/