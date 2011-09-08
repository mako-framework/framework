<?php

namespace mako
{
	/**
	* A simple curl abstraction layer.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Curl
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		/**
		* Default curl options.
		*/

		protected static $defaultOptions = array
		(
			CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; Mako Framework; +http://makoframework.com)',
			CURLOPT_RETURNTRANSFER => true
		);

		/**
		* Array holding information about the last transfer.
		*/

		protected static $info;

		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------

		/**
		* Protected constructor since this is a static class.
		*
		* @access  protected
		*/

		protected function __construct()
		{
			// Nothing here
		}

		//---------------------------------------------
		// Class methods
		//---------------------------------------------

		/**
		* Performs a curl GET request.
		*
		* @access  public
		* @param   string  The URL to fetch
		* @param   array   (optional) An array specifying which options to set and their values
		* @return  string
		*/

		public static function get($url, array $options = null)
		{
			$handle = curl_init($url);

			$options = (array) $options + static::$defaultOptions;

			curl_setopt_array($handle, $options);

			$response = curl_exec($handle);

			static::$info = curl_getinfo($handle);

			curl_close($handle);

			return $response;
		}

		/**
		* Performs a curl POST request.
		*
		* @access  public
		* @param   string   The URL to fetch
		* @param   array    (optional) An array with the field name as key and field data as value
		* @param   boolean  (optional) True to send data as multipart/form-data and false to send as application/x-www-form-urlencoded
		* @param   array    (optional) An array specifying which options to set and their values
		* @return  string
		*/

		public static function post($url, array $data = null, $multipart = false, array $options = null)
		{
			$handle = curl_init($url);

			$options = (array) $options + static::$defaultOptions;

			$options[CURLOPT_POST]       = true;
			$options[CURLOPT_POSTFIELDS] = ($multipart === true) ? (array) $data : http_build_query((array) $data);

			curl_setopt_array($handle, $options);

			$response = curl_exec($handle);

			static::$info = curl_getinfo($handle);

			curl_close($handle);

			return $response;
		}

		/**
		* Gets information about the last transfer.
		*
		* @access  public
		* @param   string  (optional) Array key of the array returned by curl_getinfo()
		* @return  mixed
		*/

		public static function getInfo($value = null)
		{
			if(empty(static::$info))
			{
				return false;
			}
			
			return ($value === null) ? static::$info : static::$info[$value];
		}
	}
}

/** -------------------- End of file --------------------**/