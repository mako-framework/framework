<?php

namespace mako
{
	use \Mako;
	use \mako\Curl;
	use \mako\recaptcha\Exception as ReCaptchaException;
	
	/**
	* Class that makes it easy to implement reCAPTCHA in your application.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class ReCaptcha
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		/**
		* Url to the api server.
		*/

		const API_SERVER = 'http://api.recaptcha.net';

		/**
		* Url to the secure api server.
		*/

		const API_SECURE_SERVER = 'https://api-secure.recaptcha.net';

		/**
		* Url to the verification server.
		*/

		const API_VERIFICATION_SERVER = 'http://api-verify.recaptcha.net/verify';

		/**
		* Array holding reCAPTCHA config.
		*/

		protected $config;

		/**
		* Response from the reCAPTCHA server.
		*/

		protected $response;

		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------

		/**
		* Constructor.
		*
		* @access  public
		*/

		public function __construct()
		{
			$this->config = Mako::config('recaptcha');
			
			if($this->config['public_key'] === '' || $this->config['private_key'] === '')
			{
				throw new ReCaptchaException(__CLASS__.': No API key defined in the config.');
			}
		}

		//---------------------------------------------
		// Class methods
		//---------------------------------------------

		/**
		* Returns reCAPTCHA HTML.
		*
		* @access  public
		* @param   boolean  (optional) False to use http and true to use https
		* @return  string
		*/

		public function getHtml($ssl = false)
		{
			$server = ($ssl === true) ? static::API_SECURE_SERVER : static::API_SERVER;
			
			$html  = "<script>var RecaptchaOptions = {theme : '{$this->config['theme']}', lang : '{$this->config['language']}'};</script>";
			$html .= "<script type=\"text/javascript\" src=\"{$server}/challenge?k={$this->config['public_key']}\"></script>\n";
			$html .= "<noscript>\n<iframe src=\"{$server}/noscript?k={$this->config['public_key']}\" height=\"250\" width=\"500\" frameborder=\"0\"></iframe><br />\n";
			$html .= "<textarea name=\"recaptcha_challenge_field\" rows=\"3\" cols=\"40\"></textarea>\n";
			$html .= "<input type=\"hidden\" name=\"recaptcha_response_field\" value=\"manual_challenge\" />\n</noscript>\n";

			return $html;
		}

		/**
		* Validates the user input and returns an array containing a boolean value and a message from the server.
		*
		* @access  public
		* @return  array
		*/

		public function validate()
		{
			if(empty($this->response))
			{
				$challenge = !empty($_REQUEST['recaptcha_challenge_field']) ? $_REQUEST['recaptcha_challenge_field'] : null;
				$response  = !empty($_REQUEST['recaptcha_response_field']) ? $_REQUEST['recaptcha_response_field'] : null;

				if($challenge === null || $response === null)
				{
					// Automatically fail check if fields are empty

					$this->response['valid']   = false;
					$this->response['message'] = 'incorrect-captcha-sol';
				}
				else
				{
					// Send request to reCaptcha server

					$data = array
					(
						'privatekey' => $this->config['private_key'],
						'remoteip'   => $_SERVER['REMOTE_ADDR'],
						'challenge'  => $challenge,
						'response'   => $response
					);

					$response = Curl::post(static::API_VERIFICATION_SERVER, $data);

					$response = explode("\n", $response);

					$this->response['valid']   = ($response[0] === 'true') ? true : false;
					$this->response['message'] = $response[1];
				}
			}

			return $this->response;
		}

		/**
		* Returns true if check was successful and false if it failed.
		*
		* @access  public
		* @return  boolean
		*/

		public function isValid()
		{
			if(empty($this->response))
			{
				$this->validate();
			}

			return $this->response['valid'];
		}
	}
}

/** -------------------- End of file --------------------**/