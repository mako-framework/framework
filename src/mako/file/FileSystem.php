<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\file;

use FilesystemIterator;
use SplFileObject;

use function dirname;
use function disk_free_space;
use function disk_total_space;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function filesize;
use function getcwd;
use function glob;
use function is_dir;
use function is_file;
use function is_readable;
use function is_writable;
use function mkdir;
use function pathinfo;
use function rename;
use function rmdir;
use function unlink;

/**
 * File system.
 */
class FileSystem
{
	/**
	 * Returns TRUE if a file exists and FALSE if not.
	 *
	 * @param  string $file Path to file
	 * @return bool
	 */
	public function has(string $file): bool
	{
		return file_exists($file);
	}

	/**
	 * Returns TRUE if the provided path is a file and FALSE if not.
	 *
	 * @param  string $file Path to file
	 * @return bool
	 */
	public function isFile(string $file): bool
	{
		return is_file($file);
	}

	/**
	 * Returns TRUE if the provided path is a directory and FALSE if not.
	 *
	 * @param  string $directory Path to directory
	 * @return bool
	 */
	public function isDirectory(string $directory): bool
	{
		return is_dir($directory);
	}

	/**
	 * Returns TRUE if a file or directory is empty and FALSE if not.
	 *
	 * @param  string $path Path to directory
	 * @return bool
	 */
	public function isEmpty(string $path): bool
	{
		if(is_dir($path))
		{
			return (new FilesystemIterator($path))->valid() === false;
		}

		return filesize($path) === 0;
	}

	/**
	 * Returns TRUE if the file is readable and FALSE if not.
	 *
	 * @param  string $file Path to file
	 * @return bool
	 */
	public function isReadable(string $file): bool
	{
		return is_readable($file);
	}

	/**
	 * Returns TRUE if the file or directory is writable and FALSE if not.
	 *
	 * @param  string $file Path to file
	 * @return bool
	 */
	public function isWritable(string $file): bool
	{
		return is_writable($file);
	}

	/**
	 * Returns the time (unix timestamp) the file was last modified.
	 *
	 * @param  string $file Path to file
	 * @return int
	 */
	public function lastModified(string $file): int
	{
		return filemtime($file);
	}

	/**
	 * Returns the fize of the file in bytes.
	 *
	 * @param  string $file Path to file
	 * @return int
	 */
	public function size(string $file): int
	{
		return filesize($file);
	}

	/**
	 * Returns the extension of the file.
	 *
	 * @param  string $file Path to file
	 * @return string
	 */
	public function extension(string $file): string
	{
		return pathinfo($file, PATHINFO_EXTENSION);
	}

	/**
	 * Renames a file or directory.
	 *
	 * @param  string $oldName Old name
	 * @param  string $newName New name
	 * @return bool
	 */
	public function rename(string $oldName, string $newName): bool
	{
		return rename($oldName, $newName);
	}

	/**
	 * Deletes the file from disk.
	 *
	 * @param  string $file Path to file
	 * @return bool
	 */
	public function remove(string $file): bool
	{
		return unlink($file);
	}

	/**
	 * Deletes a directory and its contents from disk.
	 *
	 * @param  string $path Path to directory
	 * @return bool
	 */
	public function removeDirectory(string $path): bool
	{
		foreach(new FilesystemIterator($path) as $item)
		{
			if($item->isDir())
			{
				$this->removeDirectory($item->getPathname());
			}
			else
			{
				unlink($item->getPathname());
			}
		}

		return rmdir($path);
	}

	/**
	 * Returns an array of pathnames matching the provided pattern.
	 *
	 * @param  string      $pattern Patern
	 * @param  int         $flags   Flags
	 * @return array|false
	 */
	public function glob(string $pattern, int $flags = 0)
	{
		return glob($pattern, $flags);
	}

	/**
	 * Returns the contents of the file.
	 *
	 * @param  string       $file File path
	 * @return false|string
	 */
	public function get(string $file)
	{
		return file_get_contents($file);
	}

	/**
	 * Writes the supplied data to a file.
	 *
	 * @param  string    $file File path
	 * @param  mixed     $data File data
	 * @param  bool      $lock Acquire an exclusive write lock?
	 * @return false|int
	 */
	public static function put(string $file, $data, bool $lock = false)
	{
		return file_put_contents($file, $data, $lock ? LOCK_EX : 0);
	}

	/**
	 * Prepends the supplied data to a file.
	 *
	 * @param  string    $file File path
	 * @param  mixed     $data File data
	 * @param  bool      $lock Acquire an exclusive write lock?
	 * @return false|int
	 */
	public static function prepend(string $file, $data, bool $lock = false)
	{
		return file_put_contents($file, $data . file_get_contents($file), $lock ? LOCK_EX : 0);
	}

	/**
	 * Appends the supplied data to a file.
	 *
	 * @param  string    $file File path
	 * @param  mixed     $data File data
	 * @param  bool      $lock Acquire an exclusive write lock?
	 * @return false|int
	 */
	public static function append(string $file, $data, bool $lock = false)
	{
		return file_put_contents($file, $data, $lock ? FILE_APPEND | LOCK_EX : FILE_APPEND);
	}

	/**
	 * Truncates a file.
	 *
	 * @param  string $file File path
	 * @param  bool   $lock Acquire an exclusive write lock?
	 * @return bool
	 */
	public static function truncate(string $file, bool $lock = false): bool
	{
		return (0 === file_put_contents($file, null, $lock ? LOCK_EX : 0));
	}

	/**
	 *  Creates a directory.
	 *
	 * @param  string $path      Path to directory
	 * @param  int    $mode      Mode
	 * @param  bool   $recursive Recursive
	 * @return bool
	 */
	public function createDirectory(string $path, int $mode = 0777, bool $recursive = false): bool
	{
		return mkdir($path, $mode, $recursive);
	}

	/**
	 * Returns the total size of a filesystem or disk partition in bytes.
	 *
	 * @param  string|null $directory A directory of the filesystem or disk partition
	 * @return float
	 */
	public function getDiskSize(?string $directory = null): float
	{
		return disk_total_space($directory ?? (getcwd() ?: dirname(__FILE__)));
	}

	/**
	 * Returns the total number of available bytes on the filesystem or disk partition.
	 *
	 * @param  string|null $directory A directory of the filesystem or disk partition
	 * @return float
	 */
	public function getFreeSpaceOnDisk(?string $directory = null): float
	{
		return disk_free_space($directory ?? (getcwd() ?: dirname(__FILE__)));
	}

	/**
	 * Includes a file.
	 *
	 * @param  string $file Path to file
	 * @return mixed
	 */
	public function include(string $file)
	{
		return include $file;
	}

	/**
	 * Includes a file it hasn't already been included.
	 *
	 * @param  string $file Path to file
	 * @return mixed
	 */
	public function includeOnce(string $file)
	{
		return include_once $file;
	}

	/**
	 * Requires a file.
	 *
	 * @param  string $file Path to file
	 * @return mixed
	 */
	public function require(string $file)
	{
		return require $file;
	}

	/**
	 * Requires a file if it hasn't already been required.
	 *
	 * @param  string $file Path to file
	 * @return mixed
	 */
	public function requireOnce(string $file)
	{
		return require_once $file;
	}

	/**
	 * Returns a FileInfo object.
	 *
	 * @param  string              $file Path to file
	 * @return \mako\file\FileInfo
	 */
	public function info(string $file): FileInfo
	{
		return new FileInfo($file);
	}

	/**
	 * Returns a SplFileObject.
	 *
	 * @param  string         $file           Path to file
	 * @param  string         $openMode       Open mode
	 * @param  bool           $useIncludePath Use include path?
	 * @return \SplFileObject
	 */
	public function file(string $file, string $openMode = 'r', bool $useIncludePath = false)
	{
		return new SplFileObject($file, $openMode, $useIncludePath);
	}
}
