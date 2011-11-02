<?php

namespace mako
{
	use \mako\Mako;
	
	/**
	* Mako response class.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/
	
	class Response
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------
		
		/**
		* Holds singleton instance.
		*/
		
		protected static $instance = null;
		
		/**
		* Asset location.
		*/
		
		protected $assetLocation;

		/**
		* Check ETag?
		*/

		protected $checkEtag = false;
		
		/**
		* Compress output?
		*/
		
		protected $compressOutput;
		
		/**
		* List of HTTP status codes.
		*/
		
		protected $statusCodes = array
		(
			// 1xx Informational
			
			'100' => 'Continue',
			'101' => 'Switching Protocols',
			'102' => 'Processing',
			
			// 2xx Success
			
			'200' => 'OK',
			'201' => 'Created',
			'202' => 'Accepted',
			'203' => 'Non-Authoritative Information',
			'204' => 'No Content',
			'205' => 'Reset Content',
			'206' => 'Partial Content',
			'207' => 'Multi-Status',
			
			// 3xx Redirection
			
			'300' => 'Multiple Choices',
			'301' => 'Moved Permanently',
			'302' => 'Found',
			'303' => 'See Other',
			'304' => 'Not Modified',
			'305' => 'Use Proxy',
			//'306' => 'Switch Proxy',
			'307' => 'Temporary Redirect',
			
			// 4xx Client Error
			
			'400' => 'Bad Request',
			'401' => 'Unauthorized',
			'402' => 'Payment Required',
			'403' => 'Forbidden',
			'404' => 'Not Found',
			'405' => 'Method Not Allowed',
			'406' => 'Not Acceptable',
			'407' => 'Proxy Authentication Required',
			'408' => 'Request Timeout',
			'409' => 'Conflict',
			'410' => 'Gone',
			'411' => 'Length Required',
			'412' => 'Precondition Failed',
			'413' => 'Request Entity Too Large',
			'414' => 'Request-URI Too Long',
			'415' => 'Unsupported Media Type',
			'416' => 'Requested Range Not Satisfiable',
			'417' => 'Expectation Failed',
			'418' => 'I\'m a teapot',
			'421' => 'There are too many connections from your internet address',
			'422' => 'Unprocessable Entity',
			'423' => 'Locked',
			'424' => 'Failed Dependency',
			'425' => 'Unordered Collection',
			'426' => 'Upgrade Required',
			'449' => 'Retry With',
			'450' => 'Blocked by Windows Parental Controls',
			
			// 5xx Server Error
			
			'500' => 'Internal Server Error',
			'501' => 'Not Implemented',
			'502' => 'Bad Gateway',
			'503' => 'Service Unavailable',
			'504' => 'Gateway Timeout',
			'505' => 'HTTP Version Not Supported',
			'506' => 'Variant Also Negotiates',
			'507' => 'Insufficient Storage',
			'509' => 'Bandwidth Limit Exceeded',
			'510' => 'Not Extended',
			'530' => 'User access denied',
		);
		
		/**
		* Output filter (callback function).
		*/
		
		protected $outputFilter;
		
		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------
		
		/**
		* Protected constructor to enforce singleton behavior.
		*
		* @access  protected
		*/
		
		protected function __construct()
		{
			$config = Mako::config('response');
			
			$this->assetLocation  = $config['asset_location'];
			$this->compressOutput = $config['compress_output'];
		}
		
		/**
		* Protected clone method to enforce singleton behavior.
		*
		* @access  protected
		*/
		
		protected function __clone()
		{
			// Nothing here.
		}
		
		//---------------------------------------------
		// Class methods
		//---------------------------------------------
		
		/**
		* Returns singleton instance.
		*
		* @access  public
		* @return   Response
		*/
		
		public static function instance()
		{
			if(static::$instance === null)
			{
				static::$instance = new static();
			}
			
			return static::$instance;
		}
		
		/**
		* Adds output filter that all output will be passed through before being sent.
		*
		* @access  public
		* @param   callback  Callback function used to filter output
		*/
		
		public function outputFilter($filter)
		{
			$this->outputFilter = $filter;
		}
		
		/**
		* Sends HTTP status header.
		*
		* @access  public
		* @param   int     HTTP status code
		*/
		
		public function status($statusCode)
		{
			if(isset($this->statusCodes[$statusCode]))
			{
				$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
				
				header($protocol . ' ' . $statusCode . ' '. $this->statusCodes[$statusCode]);
			}
		}
		
		/**
		* Redirects to another location.
		*
		* @access  public
		* @param   string  Location
		* @param   int     HTTP status code
		*/
		
		public function redirect($location = '', $statusCode = 302)
		{
			$this->status($statusCode);

			if(strpos($location, '://') === false)
			{
				$location = Mako::url($location);
			}
			
			header('Location: ' . $location);
			
			exit();
		}

		/**
		* Will enable response cache using ETags.
		*
		* @access  public
		*/

		public function cache()
		{
			$this->checkEtag = true;
		}
		
		/**
		* Send output to browser.
		*
		* @access  public
		*/
		
		public function send()
		{
			// Flush all output buffers except for the last one

			while(ob_get_level() > 1) ob_end_flush();

			// Get output and close last buffer

			$output = ob_get_clean();

			// Print output to browser (if there is any)

			if($output !== false && $output !== '')
			{
				$search  = array
				(
					'[mako:exe_time]',
					'[mako:assets]',
					'[mako:version]',
					'[mako:base_url]',
				);

				$replace = array
				(
					round(microtime(true) - MAKO_START, 4),
					$this->assetLocation,
					Mako::VERSION,
					Mako::url(),
				);

				$output = str_ireplace($search, $replace, $output);
				
				// Pass output through filter
				
				if(!empty($this->outputFilter))
				{
					$output = call_user_func($this->outputFilter, $output);
				}

				// Check ETag

				if($this->checkEtag === true)
				{
					$hash = '"' . sha1($output) . '"';

					header('ETag: ' . $hash);

					if(isset($_SERVER['HTTP_IF_NONE_MATCH']) && $hash === $_SERVER['HTTP_IF_NONE_MATCH'])
					{
						$this->status(304);

						return; // Don't send any output
					}
				}

				// Compress output (if enabled)

				if($this->compressOutput === true)
				{
					ob_start('ob_gzhandler');
				}

				echo $output;
			}
		}
	}
}

/** -------------------- End of file --------------------**/