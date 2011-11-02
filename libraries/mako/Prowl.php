<?php

namespace mako
{
	use \mako\Mako;
	use \mako\UTF8;
	use \mako\Curl;
	use \RuntimeException;

	/**
	* Sends messages to the Prowl (http://www.prowlapp.com/) notification system.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Prowl
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		/**
		* URL to the secure API server.
		*/

		const API_SECURE_SERVER = 'https://api.prowlapp.com/publicapi/';

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

		const HIGHT = 1;

		/**
		* Message priority.
		*/

		const EMERGENCY = 2;

		/**
		* Curl options.
		*/

		protected $curlOptions = array
		(
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
		);

		/**
		* Application identifier.
		*/
		
		protected $identifier;

		/**
		* Provider key.
		*/

		protected $providerKey;

		/**
		* API key.
		*/

		protected $apiKey;

		/**
		* Last response received from the API server.
		*/

		protected $response;

		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------
		
		/**
		* Constructor.
		*
		* @access  public
		* @param   string  (optional) Prowl configuration name
		*/

		public function __construct($name = null)
		{
			$config = Mako::config('prowl');

			$name = ($name === null) ? $config['default'] : $name;

			if(isset($config['configurations'][$name]) === false)
			{
					throw new RuntimeException(__CLASS__ . ": '{$name}' has not been defined in the prowl configuration.");
			}

			$this->identifier  = mb_substr(UTF8::convert($config['identifier']), 0, 256);
			$this->providerKey = $config['provider_key'];
			$this->apiKey      = $config['configurations'][$name]['api_key'];
		}

		/**
		* Factory method making method chaining possible right off the bat.
		*
		* @access  public
		* @param   string   (optional) Prowl configuration name
		* @return  Prowl
		*/

		public static function factory($name = null)
		{
			return new static($name);
		}

		//---------------------------------------------
		// Class methods
		//---------------------------------------------

		/**
		* Send a Prowl notification.
		*
		* @access  public
		* @param   string   The name of the event or subject of the notification.
		* @param   string   A description of the event, generally terse.
		* @param   string   (optional) URL which will be attached to the notification.
		* @param   int      (optional) Message priority
		* @return  boolean
		*/

		public function notify($event, $description, $url = null, $priority = Prowl::NORMAL)
		{
			$data = array
			(
				'apikey'      => $this->apiKey,
				'priority'    => $priority,
				'application' => $this->identifier,
				'event'       => mb_substr(UTF8::convert($event), 0, 1024),
				'description' => mb_substr(UTF8::convert($description), 0, 10000),
			);

			!empty($url) && $data['url'] = mb_substr(UTF8::convert($url), 0, 512);

			!empty($this->providerKey) && $data['providerkey'] = $this->providerKey;

			$this->response = Curl::post(static::API_SECURE_SERVER . 'add', $data, false, $this->curlOptions);

			return (Curl::getInfo('http_code') === 200) ? true : false;
		}

		/**
		* Verify if an API key is valid.
		* 
		* @access  public
		* @param   string   API key to validate.
		* @return  boolean
		*/

		public function verify($apiKey)
		{
			$data = array
			(
				'apikey' => $apiKey,
			);

			!empty($this->providerKey) && $data['providerkey'] = $this->providerKey;

			$this->response = Curl::get(static::API_SECURE_SERVER . 'verify?' . http_build_query($data), $this->curlOptions);

			return (Curl::getInfo('http_code') === 200) ? true : false;
		}

		/**
		* Returns an associative array with the registration token and URL.
		*
		* @access  public
		* @return  array
		*/

		public function retrieveToken()
		{
			if(empty($this->providerKey))
			{
				throw new RuntimeException(__CLASS__ . ": A provider key is required.");
			}

			$data = array
			(
				'providerkey' => $this->providerKey,
			);

			$this->response = Curl::get(static::API_SECURE_SERVER . 'retrieve/token?' . http_build_query($data), $this->curlOptions);

			preg_match('/<retrieve token="(.*)" url="(.*)" \/>/', $this->response, $matches);

			if(count($matches) !== 3)
			{
				return false;
			}

			return array('token' => $matches[1], 'url' => $matches[2]);
		}

		/**
		* Get an API key from the token retrieved by retrieveToken.
		*
		* @access  public
		* @param   string  Registration token
		* @return  string
		*/

		public function retrieveApiKey($token)
		{
			if(empty($this->providerKey))
			{
				throw new RuntimeException(__CLASS__ . ": A provider key is required.");
			}

			$data = array
			(
				'providerkey' => $this->providerKey,
				'token'       => $token,
			);

			$this->response = Curl::get(static::API_SECURE_SERVER . 'retrieve/apikey?' . http_build_query($data), $this->curlOptions);

			preg_match('/<retrieve apikey="(.*)" \/>/', $this->response, $matches);

			if(count($matches) !== 2)
			{
				return false;
			}

			return $matches[1];
		}

		/**
		* Returns the last response received from the API server.
		*
		* @access  public
		* @return  string
		*/

		public function getResponse()
		{
			return $this->response;
		}
	}
}

/** -------------------- End of file --------------------**/