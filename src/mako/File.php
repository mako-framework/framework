<?php

namespace mako;

use \mako\Config;
use \Closure;
use \RuntimeException;

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
				$terms = array('byte', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
			}
			else
			{
				$base  = 1000;
				$terms = array('byte', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
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
	 * Display a file in the browser.
	 *
	 * @access  public
	 * @param   string   $file         Full path to file
	 * @param   string   $contentType  (optional) Content type of the file
	 * @param   string   $filename     (optional) Filename of the download
	 * @param   Closure  $callback     (optional) Callback that will be executed after the file has been sent
	 */

	public static function display($file, $contentType = null, $filename = null, Closure $callback = null)
	{
		// Check that the file exists and that its readable

		if(file_exists($file) === false || is_readable($file) === false)
		{
			throw new RuntimeException(vsprintf("%s(): Failed to open stream.", array(__METHOD__)));
		}

		// Empty output buffers

		while(ob_get_level() > 0) ob_end_clean();

		// Send headers
		
		if($contentType === null)
		{
			$contentType = static::mime($file);
		}

		if($filename === null)
		{
			$filename = basename($file);
		}

		header('Content-type: ' . $contentType);
		header('Content-Disposition: inline; filename="' . $filename . '"');
		header('Content-Length: ' . filesize($file));

		// Read file and write to output

		readfile($file);

		// Execute callback after the file has been sent

		if($callback !== null)
		{
			$callback($file);
		}

		exit();
	}

	/**
	 * Forces a file to be downloaded.
	 *
	 * @access  public
	 * @param   string   $file         Full path to file
	 * @param   string   $contentType  (optional) Content type of the file
	 * @param   string   $filename     (optional) Filename of the download
	 * @param   int      $kbps         (optional) Max download speed in KiB/s
	 * @param   Closure  $callback     (optional) Callback that will be executed after the file has been sent
	 */

	public static function download($file, $contentType = null, $filename = null, $kbps = 0, Closure $callback = null)
	{
		// Check that the file exists and that its readable

		if(file_exists($file) === false || is_readable($file) === false)
		{
			throw new RuntimeException(vsprintf("%s(): Failed to open stream.", array(__METHOD__)));
		}

		// Empty output buffers

		while(ob_get_level() > 0) ob_end_clean();

		// Send headers
		
		if($contentType === null)
		{
			$contentType = static::mime($file);
		}

		if($filename === null)
		{
			$filename = basename($file);
		}

		header('Content-type: ' . $contentType);
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Content-Length: ' . filesize($file));

		// Read file and write it to the output

		set_time_limit(0);

		if($kbps === 0)
		{
			readfile($file);
		}
		else
		{
			$handle = fopen($file, 'r');

			while(!feof($handle) && !connection_aborted())
			{
				$s = microtime(true);

				echo fread($handle, round($kbps * 1024));

				if(($wait = 1e6 - (microtime(true) - $s)) > 0)
				{
					usleep($wait);
				}
				
			}

			fclose($handle);
		}

		// Execute callback after the file has been sent

		if($callback !== null)
		{
			$callback($file);
		}

		exit();
	}

	/**
	 * Splits a file into multiple pieces.
	 *
	 * @access  public
	 * @param   string  $file  Full path to file
	 * @param   int     $size  (optional) Piece size in MiB
	 */

	public static function split($file, $size = 10)
	{
		if(file_exists($file) === false || is_readable($file) === false)
		{
			throw new RuntimeException(vsprintf("%s(): Failed to open stream.", array(__METHOD__)));
		}

		$read = 0; // Number of bytes read

		$piece = 1; // Current piece

		$length = 1024 * 8; // Number of bytes to read

		$size = floor($size * 1024 * 1024); // Max size of each piece

		$handle = fopen($file, 'rb');

		while(!feof($handle))
		{
			$data = fread($handle, $length);

			if(strlen($data) === 0)
			{
				break; // Prevents empty files
			}

			file_put_contents($file . '.' . str_pad($piece, 4, '0', STR_PAD_LEFT), $data, FILE_APPEND);

			$read += $length;

			if($read >= $size)
			{
				$piece++;

				$read = 0;
			}
		}

		fclose($handle);
	}

	/**
	 * Merge files that have been split using the File::split method.
	 *
	 * @access  public
	 * @param   string  $file  Full path to file
	 */

	public static function merge($file)
	{
		if(file_exists($file . '.0001') === false || is_readable($file . '.0001') === false)
		{
			throw new RuntimeException(vsprintf("%s(): Failed to open stream.", array(__METHOD__)));
		}

		$piece = 1; // Current piece
		
		$length = 1024 * 8; // Number of bytes to read

		while(is_file($file . '.' . str_pad($piece, 4, '0', STR_PAD_LEFT)))
		{
			$handle = fopen($file . '.' . str_pad($piece, 4, '0', STR_PAD_LEFT), 'rb');

			while(!feof($handle))
			{
				file_put_contents($file, fread($handle, $length), FILE_APPEND);
			}

			fclose($handle);

			$piece++;
		}
	}
}

/** -------------------- End of file -------------------- **/