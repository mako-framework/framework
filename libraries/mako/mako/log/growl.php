<?php

namespace mako\log
{
	use \Mako;
	use \mako\Log;
	use \mako\Growl as Grrr;
	
	/**
	* Growl adapter.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/
	
	class Growl
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------
		
		/**
		*Growl server hostname or IP.
		*/
		
		protected $host;
		
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
			$this->host     = $config['host'];
			$this->password = $config['password'];
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
			$id = md5(uniqid());
			
			Mako::config('growl')->configurations[$id] = array
			(
				'host'          => $this->host,
				'password'      => $this->password,
				'notifications' => array_fill_keys(array_values($this->types), true),
			);
			
			$priorities = array_combine(array_keys($this->types), array(2, 1, 2, 1, 1, 0, 0, 0));
			
			Grrr::instance($id)->notify($this->types[$type], $this->types[$type], $message, false, $priorities[$type]);			
						
			unset(Mako::config('growl')->configurations[$id]); // Unset so it doesn't get cached
		}
	}
}

/** -------------------- End of file --------------------**/