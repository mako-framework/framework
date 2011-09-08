<?php

namespace mako
{
	use \Mako;
	use \mako\UTF8;
	use \mako\growl\Exception as GrowlException;
	
	/**
	* Sends messages to the Growl (http://growl.info/) notification system.
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
		* Message priority.
		*/
		
		const LOW = -2;
		
		/**
		* Message priority.
		*/
		
		const MODERATE = -1;
		
		/**
		* Message priority.
		*/
		
		const NORMAL = 0;
		
		/**
		* Message priority.
		*/
		
		const HIGH = 1;
		
		/**
		* Message priority.
		*/
		
		const EMERGENCY = 2;
		
		/**
		* Growl port.
		*/
		
		const PORT = 9887;
		
		/**
		* Holds the configuration.
		*/

		protected static $config;
		
		/**
		* Instances.
		*/
		
		protected static $instances = array();
		
		/**
		* Application identifier.
		*/
		
		protected $identifier;
		
		/**
		* Host address.
		*/
		
		protected $host;
		
		/**
		* Server password.
		*/
		
		protected $password;
		
		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------
		
		/**
		* Constructor.
		*
		* @access  public
		* @param   array   Configuration
		*/
		
		protected function __construct(array $config)
		{
			$this->identifier = UTF8::clean(static::$config['identifier']);
			$this->host       = $config['host'];
			$this->password   = $config['password'];
			
			$this->register($config['notifications']);
		}
		
		//---------------------------------------------
		// Class methods
		//---------------------------------------------
		
		/**
		* Returns an instance of the requested Frowl configuration.
		*
		* @param   string  (optional) Growl configuration name
		* @return  Growl
		*/
		
		public static function instance($name = null)
		{
			if(isset(static::$instances[$name]))
			{
				return static::$instances[$name];
			}
			else
			{
				if(empty(static::$config))
				{
					static::$config = Mako::config('growl');
				}
				
				$name = ($name === null) ? static::$config['default'] : $name;
				
				if(isset(static::$config['configurations'][$name]) === false)
				{
					throw new GrowlException(__CLASS__.": '{$name}' has not been defined in the growl configuration.");
				}
				
				static::$instances[$name] = new static(static::$config['configurations'][$name]);
				
				return static::$instances[$name];
			}
		}
		
		/**
		* Sends data to the Growl server.
		*
		* @access  protected
		* @param   string     Data to send
		*/
		
		protected function send($data)
		{
			if(function_exists('socket_create') && function_exists('socket_sendto'))
			{
				$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
				
				socket_sendto($socket, $data, strlen($data), 0x100, $this->host, static::PORT);
				
				socket_close($socket);
			}
			else if(function_exists('fsockopen'))
			{
				$socket = fsockopen('udp://' . $this->host, static::PORT);
				
				fwrite($socket, $data);
				
				fclose($socket);
			}
		}
		
		/**
		* Registers the application with the Growl server.
		*
		* @access  protected
		* @param   array      Array of notification types.
		*/
		
		protected function register(array $notifications)
		{
			$data         = '';
			$count        = 0;
			$defaults     = '';
			
			foreach($notifications as $key => $value)
			{
				$notification = UTF8::clean($key);
				
				$data .= pack('n', strlen($notification)) . $notification;
				
				if($value === true)
				{
					$defaults .= pack('c', $count);

					$count++;
				}
			}
						
			$data  = pack('c2nc2', 1, 0, strlen($this->identifier), count($notifications), $count) . $this->identifier . $data . $defaults;
			$data .= pack('H32', md5($data . $this->password));
			
			$this->send($data);
		}
		
		/**
		* Sends a message to the Growl server.
		*
		* @access  public
		* @param   string   Notification type
		* @param   string   Notification title
		* @param   string   Notification message
		* @param   boolean  Make notification sticky?
		* @param   int      Notification priority
		*/
		
		public function notify($notification, $title, $message, $sticky = false, $priority = Growl::NORMAL)
		{
			$notification  = UTF8::clean($notification);
			$title         = UTF8::clean($title);
			$message       = UTF8::clean($message);
			$priority      = (int) $priority;

			$flags = ($priority & 7) * 2;
			
			if($priority < 0)
			{
				$flags |= 8;
			}
			
			if($sticky === true)
			{
				$flags |= 256;
			}

			$data  = pack('c2n5', 1, 1, $flags, strlen($notification), strlen($title), strlen($message), strlen($this->identifier));
			$data .= $notification . $title . $message . $this->identifier;
			$data .= pack('H32', md5($data . $this->password));
			
			$this->send($data);
		}
	}
}

/** -------------------- End of file --------------------**/