<?php

namespace mako
{
	use \mako\Mako;
	use \mako\UTF8;
	use \RuntimeException;
	
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
		* GNTP version.
		*/

		const VERSION = '1.0';

		/**
		* CRLF.
		*/

		const CRLF = "\r\n";

		/**
		* Growl port.
		*/

		const PORT = 23053;

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
		* Supported hash algorithms.
		*/

		protected $hashAlgorithms = array('md5', 'sha1', 'sha256', 'sha512');

		/**
		* Application name.
		*/

		protected $application;

		/**
		* URL to application icon.
		*/

		protected $icon;

		/**
		* Host address.
		*/

		protected $host;

		/**
		* Hash.
		*/

		protected $hash;

		/**
		* Encryption.
		*/

		protected $encryption = 'NONE';

		/**
		* Server password.
		*/

		protected $password;

		/**
		* Array of notification to register with the Growl server.
		*/

		protected $notifications;

		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------
		
		/**
		* Constructor.
		*
		* @access  public
		* @param   string  (optional) Gowl configuration name
		*/
		
		public function __construct($name = null)
		{
			$config = Mako::config('growl');

			$name = ($name === null) ? $config['default'] : $name;

			if(isset($config['configurations'][$name]) === false)
			{
				throw new RuntimeException(__CLASS__ . ": '{$name}' has not been defined in the growl configuration.");
			}

			if(!in_array($config['hash'], $this->hashAlgorithms))
			{
				throw new RuntimeException(__CLASS__ . ": Unsupported hash algorithm.");
			}

			$this->application   = UTF8::convert($config['application_name']);
			$this->icon          = UTF8::convert($config['application_icon']);
			$this->hash          = $config['hash'];
			$this->host          = $config['configurations'][$name]['host'];
			$this->password      = $config['configurations'][$name]['password'];
			$this->notifications = $config['configurations'][$name]['notifications'];

			$this->register();
		}

		/**
		* Factory method making method chaining possible right off the bat.
		*
		* @access  public
		* @param   string  (optional) Gowl configuration name
		* @return  Growl
		*/

		public static function factory($name = null)
		{
			return new static($name);
		}

		//---------------------------------------------
		// Class methods
		//---------------------------------------------

		/**
		* Sends data to the Growl server.
		*
		* @access  protected
		* @param   string     Data to send
		*/

		protected function send($type, $headers)
		{
			$socket = @fsockopen('tcp://' . $this->host, static::PORT, $errNo, $errStr);

			if(!$socket)
			{
				throw new RuntimeException(__CLASS__ . ": {$errStr}.");
			}

			$hash = empty($this->password) ? '' : $this->hash($this->password);

			$data  = trim('GNTP/' . static::VERSION . ' ' . $type . ' ' . $this->encryption . ' ' . $hash) . static::CRLF;
			$data .= 'Application-Name: ' . UTF8::convert($this->application) . static::CRLF;

			if(!empty($this->icon))
			{
				$data .= 'Application-Icon: ' . $this->icon . static::CRLF;
			}

			$data .= $headers;
			
			$data .= 'Origin-Software-Name: Mako Framework' . static::CRLF;
			$data .= 'Origin-Software-Version: ' . Mako::VERSION . static::CRLF;

			fwrite($socket, $data);

			while(($response = fgets($socket)) != false)
			{
				// Mac version of Growl doesn't work if you don't read the response
			}

			fclose($socket);
		}

		/**
		* Generate necessary hashes for authentication.
		*
		* @access  protected
		* @param   string     Growl password
		* @return  string
		*/

		protected function hash($password)
		{
			$salt = mt_rand(268435456, mt_getrandmax());

			$saltHex   = dechex($salt);
			$saltBytes = pack("H*", $saltHex);

			$passHex   = bin2hex($this->password);
			$passBytes = pack("H*", $passHex);

			$keyBasis  = $passBytes . $saltBytes;

			$key     = hash($this->hash, $keyBasis, true);
			$keyHash = hash($this->hash, $key);

			return strtoupper($this->hash . ':' . $keyHash . '.' . $saltHex);
		}

		/**
		* Converts boolean values to text equivalent.
		*
		* @access  protected
		* @param   boolean    Boolean value
		* @return  string
		*/

		protected function convertBool($bool)
		{
			return $bool ? 'True' : 'False';
		}

		/**
		* Builds and sends registration headers.
		*
		* @access  protected
		* @param   array      Array of notifications to register
		*/

		protected function register()
		{
			$headers = 'Notifications-Count: ' . count($this->notifications) . static::CRLF;
			
			$headers .= static::CRLF;

			foreach($this->notifications as $key => $value)
			{
				$headers .= 'Notification-Name: ' . UTF8::convert($key) . static::CRLF;
				$headers .= 'Notification-Display-Name: ' . UTF8::convert($key) . static::CRLF;
				$headers .= 'Notification-Enabled: ' . $this->convertBool($value) . static::CRLF;
				$headers .= static::CRLF;
			}

			$this->send('REGISTER', $headers);
		}

		/**
		* Builds and sends notification headers.
		*
		* @access  public
		* @param   string   Notification type
		* @param   string   Notification title
		* @param   string   Notification message
		* @param   boolean  (optional) Make notification sticky?
		* @param   int      (optional) Notification priority
		*/

		public function notify($notification, $title, $message, $sticky = false, $priority = Growl::NORMAL)
		{
			if(!isset($this->notifications[$notification]))
			{
				throw new RuntimeException(__CLASS__ . ": Invalid notification name. '{$notification}' has not been defined in the configuration.");
			}

			$headers  = 'Notification-Name: ' . UTF8::convert($notification) . static::CRLF;
			$headers .= 'Notification-Title: ' . UTF8::convert($title) . static::CRLF;
			$headers .= 'Notification-Text: ' . UTF8::convert($message) . static::CRLF;
			$headers .= 'Notification-Sticky: ' . $this->convertBool($sticky) . static::CRLF;
			$headers .= 'Notification-Priority: ' . (int) $priority . static::CRLF;
			$headers .= static::CRLF;

			$this->send('NOTIFY', $headers);
		}
	}
}

/** -------------------- End of file --------------------**/