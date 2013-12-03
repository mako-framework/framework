<?php

namespace mako\utility;

use \Closure;
use \mako\core\Config;
use \mako\security\crypto\Crypto;
use \mako\security\crypto\adapters\Adapter;

/**
 * Collection of file related methods.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class File
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	// Nothing here

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
	 * Returns filesize in a human friendly format.
	 *
	 * @access  public
	 * @param   int|string  $size    Path to file or size in bytes
	 * @param   boolean     $binary  (optional) True to use binary prefixes and false to use decimal prefixes
	 * @return  string
	 */

	public static function size($size, $binary = true)
	{
		if(!is_int($size) && is_file($size))
		{
			$size = filesize($size);
		}

		if($size > 0)
		{
			if($binary === true)
			{
				$base  = 1024;
				$terms = ['byte', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
			}
			else
			{
				$base  = 1000;
				$terms = ['byte', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
			}

			$e = floor(log($size, $base));

			return round($size / pow($base, $e), 2) . ' ' . $terms[$e];
		}
		else
		{
			return '0 byte';
		}
	}
	
	/**
	 * Returns the mime type of a file. Returns false if the mime type is not found.
	 *
	 * @access  public
	 * @param   string   $file   Full path to the file
	 * @param   boolean  $guess  (optional) Set to false to disable mime type guessing
	 * @return  string
	 */
	
	public static function mime($file, $guess = true)
	{
		if(function_exists('finfo_open'))
		{
			// Get mime using the file information functions
			
			$info = finfo_open(FILEINFO_MIME_TYPE);
			
			$mime = finfo_file($info, $file);
			
			finfo_close($info);
			
			return $mime;
		}
		else
		{
			if($guess === true)
			{
				// Just guess mime by using the file extension

				$extension = pathinfo($file, PATHINFO_EXTENSION);

				return Config::get('mimes.' . $extension, false);
			}
			else
			{
				return false;
			}
		}
	}

	/**
	 * Returns the contents of the file.
	 * 
	 * @access  public
	 * @param   string          $file  File path
	 * @return  string|boolean
	 */

	public static function get($file)
	{
		return file_get_contents($file);
	}

	/**
	 * Writes the supplied data to a file.
	 * 
	 * @access  public
	 * @param   string       $file  File path
	 * @param   mixed        $data  File data
	 * @param   boolean      $lock  (optional) Acquire an exclusive write lock?
	 * @return  int|boolean
	 */

	public static function put($file, $data, $lock = false)
	{
		return file_put_contents($file, $data, $lock ? LOCK_EX : 0);
	}

	/**
	 * Prepends the supplied data to a file.
	 * 
	 * @access  public
	 * @param   string       $file  File path
	 * @param   mixed        $data  File data
	 * @param   boolean      $lock  (optional) Acquire an exclusive write lock?
	 * @return  int|boolean
	 */

	public static function prepend($file, $data, $lock = false)
	{
		return file_put_contents($file, $data . file_get_contents($file), $lock ? LOCK_EX : 0);
	}

	/**
	 * Appends the supplied data to a file.
	 * 
	 * @access  public
	 * @param   string       $file  File path
	 * @param   mixed        $data  File data
	 * @param   boolean      $lock  (optional) Acquire an exclusive write lock?
	 * @return  int|boolean
	 */

	public static function append($file, $data, $lock = false)
	{
		return file_put_contents($file, $data,  $lock ? FILE_APPEND | LOCK_EX : FILE_APPEND);
	}

	/**
	 * Truncates a file.
	 * 
	 * @access  public
	 * @param   string   $file  File path
	 * @param   boolean  $lock  (optional) Acquire an exclusive write lock?
	 * @return  boolean
	 */

	public static function truncate($file, $lock = false)
	{
		return (0 === file_put_contents($file, null, $lock ? LOCK_EX : 0));
	}

	/**
	 * Encrypts a file.
	 * 
	 * @access  public
	 * @param   string                                   $file    File path
	 * @param   \mako\security\crypto\adapters\Adapter   $crypto  (optional) Crypto adapter instance
	 * @return  boolean
	 */

	public static function encrypt($file, Adapter $crypto = null)
	{
		$crypto = $crypto ?: Crypto::factory();

		return (file_put_contents($file, $crypto->encrypt(file_get_contents($file))) !== false);
	}

	/**
	 * Decrypts a file.
	 * 
	 * @access  public
	 * @param   string                                   $file    File path
	 * @param   \mako\security\crypto\adapters\Adapter   $crypto  (optional) Crypto adapter instance
	 * @return  boolean
	 */

	public static function decrypt($file, Adapter $crypto = null)
	{
		$crypto = $crypto ?: Crypto::factory();

		$decrypted = $crypto->decrypt(file_get_contents($file));

		if($decrypted !== false)
		{
			return (false !== file_put_contents($file, $decrypted));
		}
		else
		{
			return false;
		}
	}

	/**
	 * Encrypts and signs a file.
	 * 
	 * @access  public
	 * @param   string                                   $file    File path
	 * @param   \mako\security\crypto\adapters\Adapter   $crypto  (optional) Crypto adapter instance
	 * @return  boolean
	 */

	public static function encryptAndSign($file, Adapter $crypto = null)
	{
		$crypto = $crypto ?: Crypto::factory();

		return (file_put_contents($file, $crypto->encryptAndSign(file_get_contents($file))) !== false);
	}

	/**
	 * Validates and decrypts a file.
	 * 
	 * @access  public
	 * @param   string                                   $file    File path
	 * @param   \mako\security\crypto\adapters\Adapter   $crypto  (optional) Crypto adapter instance
	 * @return  boolean
	 */

	public static function validateAndDecrypt($file, Adapter $crypto = null)
	{
		$crypto = $crypto ?: Crypto::factory();

		$decrypted = $crypto->validateAndDecrypt(file_get_contents($file));

		if($decrypted !== false)
		{
			return (false !== file_put_contents($file, $decrypted));
		}
		else
		{
			return false;
		}
	}
}

/** -------------------- End of file -------------------- **/