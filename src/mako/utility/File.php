<?php

namespace mako\utility;

/**
 * Collection of file utlity methods.
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

	/**
	 * Mime types.
	 * 
	 * @var array
	 */

	protected static $mimes =
	[
		'aac'        => 'audio/aac',
		'atom'       => 'application/atom+xml',
		'avi'        => 'video/avi',
		'bmp'        => 'image/x-ms-bmp',
		'c'          => 'text/x-c',
		'class'      => 'application/octet-stream',
		'css'        => 'text/css',
		'csv'        => 'text/csv',
		'deb'        => 'application/x-deb',
		'dll'        => 'application/x-msdownload',
		'dmg'        => 'application/x-apple-diskimage',
		'doc'        => 'application/msword',
		'docx'       => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'exe'        => 'application/octet-stream',
		'flv'        => 'video/x-flv',
		'gif'        => 'image/gif',
		'gz'         => 'application/x-gzip',
		'h'          => 'text/x-c',
		'htm'        => 'text/html',
		'html'       => 'text/html',
		'ics'        => 'text/calendar',
		'ical'       => 'text/calendar',
		'ini'        => 'text/plain',
		'jar'        => 'application/java-archive',
		'java'       => 'text/x-java',
		'jpeg'       => 'image/jpeg',
		'jpg'        => 'image/jpeg',
		'js'         => 'text/javascript',
		'json'       => 'application/json',
		'jp2'        => 'image/jp2',
		'mid'        => 'audio/midi',
		'midi'       => 'audio/midi',
		'mka'        => 'audio/x-matroska',
		'mkv'        => 'video/x-matroska',
		'mp3'        => 'audio/mpeg',
		'mp4'        => 'video/mp4',
		'mpeg'       => 'video/mpeg',
		'mpg'        => 'video/mpeg',
		'm4a'        => 'video/mp4',
		'm4v'        => 'video/mp4',
		'odt'        => 'application/vnd.oasis.opendocument.text',
		'ogg'        => 'audio/ogg',
		'pdf'        => 'application/pdf',
		'php'        => 'text/x-php',
		'png'        => 'image/png',
		'psd'        => 'image/vnd.adobe.photoshop',
		'py'         => 'application/x-python',
		'ra'         => 'audio/vnd.rn-realaudio',
		'ram'        => 'audio/vnd.rn-realaudio',
		'rar'        => 'application/x-rar-compressed',
		'rss'        => 'application/rss+xml',
		'safariextz' => 'application/x-safari-extension',
		'sh'         => 'text/x-shellscript',
		'shtml'      => 'text/html',
		'swf'        => 'application/x-shockwave-flash',
		'tar'        => 'application/x-tar',
		'tif'        => 'image/tiff',
		'tiff'       => 'image/tiff',
		'torrent'    => 'application/x-bittorrent',
		'txt'        => 'text/plain',
		'wav'        => 'audio/wav',
		'webp'       => 'image/webp',
		'wma'        => 'audio/x-ms-wma',
		'xls'        => 'application/vnd.ms-excel',
		'xml'        => 'text/xml',
		'zip'        => 'application/zip',
		'3gp'        => 'video/3gpp',
		'3g2'        => 'video/3gpp2',
	];

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
	 * Returns filesize in bytes.
	 *
	 * @access  public
	 * @return  int
	 */

	public static function size($size, $binary = true)
	{
		return filesize($size);
	}

	/**
	 * Returns the file extension.
	 * 
	 * @access  public
	 * @param   string  File path
	 * @return  string
	 */

	public static function extension($file)
	{
		return pathinfo($file, PATHINFO_EXTENSION);
	}
	
	/**
	 * Returns the mime type of a file. Returns false if the mime type is not found.
	 *
	 * @access  public
	 * @param   string   $file   File path
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

				return isset(static::$mimes[$extension]) ? static::$mimes[$extension] : false;
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
}

/** -------------------- End of file -------------------- **/