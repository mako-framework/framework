<?php

namespace mako
{
	use \mako\Mako;
	use \RuntimeException;

	/**
	* Simple Redis client based on protocol specification at http://redis.io/topics/protocol.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2012 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Redis
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		/**
		* Command terminator.
		*/

		const CRLF = "\r\n";

		/**
		* Holds the configuration.
		*/

		protected $config;

		/**
		* Socket connection.
		*/

		protected $connection;

		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------

		/**
		* Constructor.
		*
		* @access  public
		* @param   string  (optional) Redis configuration name
		*/

		public function __construct($name = null)
		{
			$config = Mako::config('redis');

			$name = ($name === null) ? $config['default'] : $name;

			if(isset($config['configurations'][$name]) === false)
			{
				throw new RuntimeException(vsprintf("%s(): '%s' has not been defined in the redis configuration.", array(__METHOD__, $name)));
			}

			$this->config = $config['configurations'][$name];

			$this->connect();
		}

		/**
		* Factory method making method chaining possible right off the bat.
		*
		* @access  public
		* @param   string  (optional) Redis configuration name
		* @return  Redis
		*/

		public static function factory($name = null)
		{
			return new static($name);
		}

		/**
		* Destructor.
		*
		* @access  public
		*/

		public function __destruct()
		{
			$this->disconnect();
		}

		//---------------------------------------------
		// Class methods
		//---------------------------------------------

		/**
		* Connects to the Redis server.
		*
		* @access  protected
		*/

		protected function connect()
		{
			$this->connection = @fsockopen('tcp://' . $this->config['host'], $this->config['port'], $errNo, $errStr);

			if(!$this->connection)
			{
				throw new RuntimeException(vsprintf("%s(): %s", array(__METHOD__, $errStr)));
			}

			if(!empty($this->config['password']))
			{
				$this->auth($this->config['password']);
			}

			if(!empty($this->config['database']) && $this->config['database'] !== 0)
			{
				$this->select($this->config['database']);
			}
		}

		/**
		* Closes connection to the Redis server.
		*
		* @access  protected
		*/

		protected function disconnect()
		{
			if(is_resource($this->connection))
			{
				fclose($this->connection);	
			}
		}

		/**
		* Returns the value of a single line reply.
		*
		* @access  protected
		* @param   string     Redis reply
		* @return  string
		*/

		protected function singleLine($response)
		{
			return substr($response, 1);
		}

		/**
		* Returns the value of a integer reply.
		*
		* @access  protected
		* @param   string     Redis reply
		* @return  int
		*/

		protected function integer($response)
		{
			return (int) substr($response, 1);
		}

		/**
		* Returns the value of a bulk reply.
		*
		* @access  protected
		* @param   string     Redis response
		* @return  string
		*/

		protected function bulk($response)
		{
			$length = (int) substr($response, 1);

			return substr(fread($this->connection, $length + strlen(static::CRLF)), 0, - strlen(static::CRLF));
		}

		/**
		* Sends command to Redis server and returns response.
		*
		* @access  public
		* @param   string  Command name
		* @param   array   Command parameters
		* @return  mixed  
		*/

		public function __call($name, $args)
		{
			// Build command

			array_unshift($args, strtoupper($name));

			$command = '*' . count($args) . static::CRLF;

			foreach($args as $arg)
			{
				$command .= '$' . strlen($arg) . static::CRLF . $arg . static::CRLF;
			}

			// Send command to server

			fwrite($this->connection, $command);

			$response = trim(fgets($this->connection));

			// Handle response

			switch(substr($response, 0, 1))
			{
				case '-': // error message
					throw new RuntimeException(vsprintf("%s(): %s.", array(__METHOD__, substr($response, 5))));
				break;
				case '+': // single line reply
					return $this->singleLine($response);
				break;
				case ':': // integer reply
					return $this->integer($response);
				break;
				case '$': // bulk reply
					if($response === '$-1')
					{
						return null;
					}
					else
					{
						return $this->bulk($response);
					}
				break;
				case '*': // multi-bulk reply
					if($response === '*-1')
					{
						return null;
					}

					$data = array();

					$count = substr($response, 1);

					for($i = 0; $i < $count; $i++)
					{
						$response = fgets($this->connection);

						switch(substr($response, 0, 1))
						{
							case ':': // integer reply
								$data[] = $this->integer($response);
							break;
							case '$': // bulk reply
								if($response === '$-1')
								{
									$data[] = null;
								}
								else
								{
									$data[] = $this->bulk($response);
								}
							break;
						}
					}

					return $data;
				break;
				default:
					throw new RuntimeException(vsprintf("%s(): Unable to handle server response.", array(__METHOD__)));
			}
		}
	}
}

/** -------------------- End of file --------------------**/