<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\file;

use DirectoryIterator;
use FilesystemIterator;
use SplFileObject;

/**
 * File system.
 *
 * @author  Frederic G. Østby
 */
class FileSystem
{
	/**
	 * Mime types.
	 *
	 * @var array
	 */
	protected $mimeTypes =
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

	/**
	 * Returns TRUE if a file exists and FALSE if not.
	 *
	 * @access  public
	 * @param   string  $file  Path to file
	 * @return  bool
	 */
	public function exists(string $file): bool
	{
		return file_exists($file);
	}

	/**
	 * Returns TRUE if the provided path is a file and FALSE if not.
	 *
	 * @access  public
	 * @param   string  $file  Path to file
	 * @return  bool
	 */
	public function isFile(string $file): bool
	{
		return is_file($file);
	}

	/**
	 * Returns TRUE if the provided path is a directory and FALSE if not.
	 *
	 * @access  public
	 * @param   string  $directory  Path to directory
	 * @return  bool
	 */
	public function isDirectory(string $directory): bool
	{
		return is_dir($directory);
	}

	/**
	 * Returns TRUE if a file or directory is empty and FALSE if not.
	 *
	 * @access  public
	 * @param   string  $path  Path to directory
	 * @return  bool
	 */
	public function isEmpty(string $path): bool
	{
		if(is_dir($path))
		{
			foreach(new DirectoryIterator($path) as $file)
			{
				if(!$file->isDot())
				{
					return false;
				}
			}

			return true;
		}

		return filesize($path) === 0;
	}

	/**
	 * Returns TRUE if the file is readable and FALSE if not.
	 *
	 * @access  public
	 * @param   string  $file  Path to file
	 * @return  bool
	 */
	public function isReadable(string $file): bool
	{
		return is_readable($file);
	}

	/**
	 * Returns TRUE if the file or directory is writable and FALSE if not.
	 *
	 * @access  public
	 * @param   string  $file  Path to file
	 * @return  bool
	 */
	public function isWritable(string $file): bool
	{
		return is_writable($file);
	}

	/**
	 * Returns the time (unix timestamp) the file was last modified.
	 *
	 * @access  public
	 * @param   string  $file  Path to file
	 * @return  int
	 */
	public function lastModified(string $file): int
	{
		return filemtime($file);
	}

	/**
	 * Returns the fize of the file in bytes.
	 *
	 * @access  public
	 * @param   string  $file  Path to file
	 * @return  int
	 */
	public function size(string $file): int
	{
		return filesize($file);
	}

	/**
	 * Returns the extention of the file.
	 *
	 * @access  public
	 * @param   string  $file  Path to file
	 * @return  string
	 */
	public function extension(string $file): string
	{
		return pathinfo($file, PATHINFO_EXTENSION);
	}

	/**
	 * Returns the mime type of the file.
	 *
	 * @access  public
	 * @param   string       $file   Path to file
	 * @param   bool         $guess  (optinal) Guess mime type if finfo_open doesn't exist?
	 * @return  string|bool
	 */
	public function mime(string $file, bool $guess = true)
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

				return $this->mimeTypes[$extension] ?? false;
			}
			else
			{
				return false;
			}
		}
	}

	/**
	 * Deletes the file from disk.
	 *
	 * @access  public
	 * @param   string  $file  Path to file
	 * @return  bool
	 */
	public function delete(string $file): bool
	{
		return unlink($file);
	}

	/**
	 * Deletes a directory and its contents from disk.
	 *
	 * @access  public
	 * @param   string  $path  Path to directory
	 * @return  bool
	 */
	public function deleteDirectory(string $path): bool
	{
		foreach(new FilesystemIterator($path) as $item)
		{
			if($item->isDir())
			{
				$this->deleteDirectory($item->getPathname());
			}
			else
			{
				$this->delete($item->getPathname());
			}
		}

		return rmdir($path);
	}

	/**
	 * Returns an array of pathnames matching the provided pattern.
	 *
	 * @access  public
	 * @param   string       $pattern  Patern
	 * @param   int          $flags    Flags
	 * @return  array|false
	 */
	public function glob(string $pattern, int $flags = 0)
	{
		return glob($pattern, $flags);
	}

	/**
	 * Returns the contents of the file.
	 *
	 * @access  public
	 * @param   string       $file  File path
	 * @return  string|bool
	 */
	public function getContents(string $file)
	{
		return file_get_contents($file);
	}

	/**
	 * Writes the supplied data to a file.
	 *
	 * @access  public
	 * @param   string    $file  File path
	 * @param   mixed     $data  File data
	 * @param   bool      $lock  Acquire an exclusive write lock?
	 * @return  int|bool
	 */
	public static function putContents(string $file, $data, bool $lock = false)
	{
		return file_put_contents($file, $data, $lock ? LOCK_EX : 0);
	}

	/**
	 * Prepends the supplied data to a file.
	 *
	 * @access  public
	 * @param   string    $file  File path
	 * @param   mixed     $data  File data
	 * @param   bool      $lock  Acquire an exclusive write lock?
	 * @return  int|bool
	 */
	public static function prependContents(string $file, $data, bool $lock = false)
	{
		return file_put_contents($file, $data . file_get_contents($file), $lock ? LOCK_EX : 0);
	}

	/**
	 * Appends the supplied data to a file.
	 *
	 * @access  public
	 * @param   string    $file  File path
	 * @param   mixed     $data  File data
	 * @param   bool      $lock  Acquire an exclusive write lock?
	 * @return  int|bool
	 */
	public static function appendContents(string $file, $data, bool $lock = false)
	{
		return file_put_contents($file, $data,  $lock ? FILE_APPEND | LOCK_EX : FILE_APPEND);
	}

	/**
	 * Truncates a file.
	 *
	 * @access  public
	 * @param   string  $file  File path
	 * @param   bool    $lock  Acquire an exclusive write lock?
	 * @return  bool
	 */
	public static function truncateContents(string $file, bool $lock = false): bool
	{
		return (0 === file_put_contents($file, null, $lock ? LOCK_EX : 0));
	}

	/**
	 *  Creates a directory.
	 *
	 *  @access  public
	 *  @param   string   $path       Path to directory
	 *  @param   int      $mode       Mode
	 *  @param   bool     $recursive  Recursive
	 *  @return  bool
	 */
	public function createDirectory(string $path, int $mode = 0777, bool $recursive = false): bool
	{
		return mkdir($path, $mode, $recursive);
	}

	/**
	 * Includes a file.
	 *
	 * @access  public
	 * @param   string  $file  Path to file
	 * @return  mixed
	 */
	public function include(string $file)
	{
		return include $file;
	}

	/**
	 * Includes a file it hasn't already been included.
	 *
	 * @access  public
	 * @param   string  $file  Path to file
	 * @return  mixed
	 */
	public function includeOnce(string $file)
	{
		return include_once $file;
	}

	/**
	 * Requires a file.
	 *
	 * @access  public
	 * @param   string  $file  Path to file
	 * @return  mixed
	 */
	public function require(string $file)
	{
		return require $file;
	}

	/**
	 * Requires a file if it hasn't already been required.
	 *
	 * @access  public
	 * @param   string  $file  Path to file
	 * @return  mixed
	 */
	public function requireOnce(string $file)
	{
		return require_once $file;
	}

	/**
	 * Generate a hash value using the contents of the given file.
	 *
	 * @access  public
	 * @param   string  $file       Path to file
	 * @param   string  $algorithm  Hashing algorithm
	 * @param   bool    $raw        Output raw binary data?
	 * @return  string
	 */
	public function hash(string $file, string $algorithm = 'sha256', bool $raw = false): string
	{
		return hash_file($algorithm, $file, $raw);
	}

	/**
	 * Generate a keyed hash value using the HMAC method.
	 *
	 * @access  public
	 * @param   string  $file       Path to file
	 * @param   string  $key        Shared secret key
	 * @param   string  $algorithm  Hashing algorithm
	 * @param   bool    $raw        Output raw binary data?
	 * @return  string
	 */
	public function hmac(string $file, string $key, string $algorithm = 'sha256', bool $raw = false): string
	{
		return hash_hmac_file($algorithm, $file, $key, $raw);
	}

	/**
	 * Returns a SplFileObject.
	 *
	 * @access  public
	 * @param   string          $file            Path to file
	 * @param   string          $openMode        Open mode
	 * @param   bool            $useIncludePath  Use include path?
	 * @return  \SplFileObject
	 */
	public function file(string $file, string $openMode = 'r', bool $useIncludePath = false)
	{
		return new SplFileObject($file, $openMode, $useIncludePath);
	}
}