<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\file;

use FilesystemIterator;
use SplFileObject;

use function chmod;
use function copy;
use function disk_free_space;
use function disk_total_space;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function fileperms;
use function filesize;
use function getcwd;
use function glob;
use function is_dir;
use function is_file;
use function is_link;
use function is_readable;
use function is_writable;
use function link;
use function mkdir;
use function pathinfo;
use function readlink;
use function rename;
use function rmdir;
use function symlink;
use function unlink;

/**
 * File system.
 */
class FileSystem
{
	/**
	 * Returns TRUE if a resource exists and FALSE if not.
	 */
	public function has(string $path): bool
	{
		return file_exists($path);
	}

	/**
	 * Returns TRUE if the provided path is a file and FALSE if not.
	 */
	public function isFile(string $path): bool
	{
		return is_file($path);
	}

	/**
	 * Returns TRUE if the provided path is a directory and FALSE if not.
	 */
	public function isDirectory(string $path): bool
	{
		return is_dir($path);
	}

	/**
	 * Returns TRUE if a file or directory is empty and FALSE if not.
	 */
	public function isEmpty(string $path): bool
	{
		if (is_dir($path)) {
			return (new FilesystemIterator($path))->valid() === false;
		}

		return filesize($path) === 0;
	}

	/**
	 * Sets the file permissions.
	 */
	public function setPermissions(string $path, Permission ...$permission): bool
	{
		return chmod($path, Permission::calculate(...$permission));
	}

	/**
	 * Returns TRUE if the file permissions contain the specified permissions and FALSE if not.
	 */
	public function hasPermission(string $path, Permission ...$permission): bool
	{
		return Permission::hasPermissions(fileperms($path) & Permission::FULL->value, ...$permission);
	}

	/**
	 * Returns TRUE if the file is readable and FALSE if not.
	 */
	public function isReadable(string $path): bool
	{
		return is_readable($path);
	}

	/**
	 * Returns TRUE if the file or directory is writable and FALSE if not.
	 */
	public function isWritable(string $path): bool
	{
		return is_writable($path);
	}

	/**
	 * Returns TRUE if the provided path is a link and FALSE if not.
	 */
	public function isLink(string $path): bool
	{
		return is_link($path);
	}

	/**
	 * Returns the target of a link.
	 */
	public function getLinkTarget(string $path): false|string
	{
		return readlink($path);
	}

	/**
	 * Creates a symbolic link.
	 */
	public function createSymbolicLink(string $path, string $linkName): bool
	{
		return symlink($path, $linkName);
	}

	/**
	 * Creates a hard link.
	 */
	public function createHardLink(string $path, string $linkName): bool
	{
		return link($path, $linkName);
	}

	/**
	 * Returns the time (unix timestamp) the file was last modified.
	 */
	public function lastModified(string $path): int
	{
		return filemtime($path);
	}

	/**
	 * Returns the fize of the file in bytes.
	 */
	public function size(string $path): int
	{
		return filesize($path);
	}

	/**
	 * Returns the extension of the file.
	 */
	public function extension(string $path): string
	{
		return pathinfo($path, PATHINFO_EXTENSION);
	}

	/**
	 * Copies a file.
	 */
	public function copy(string $source, string $destination): bool
	{
		return copy($source, $destination);
	}

	/**
	 * Renames a file or directory.
	 */
	public function rename(string $oldName, string $newName): bool
	{
		return rename($oldName, $newName);
	}

	/**
	 * Deletes the file from disk.
	 */
	public function remove(string $path): bool
	{
		return unlink($path);
	}

	/**
	 * Deletes a directory and its contents from disk.
	 */
	public function removeDirectory(string $path): bool
	{
		foreach (new FilesystemIterator($path) as $item) {
			if ($item->isDir()) {
				$this->removeDirectory($item->getPathname());

				continue;
			}

			unlink($item->getPathname());
		}

		return rmdir($path);
	}

	/**
	 * Returns an array of pathnames matching the provided pattern.
	 */
	public function glob(string $pattern, int $flags = 0): array|false
	{
		return glob($pattern, $flags);
	}

	/**
	 * Returns the contents of the file.
	 */
	public function get(string $path): false|string
	{
		return file_get_contents($path);
	}

	/**
	 * Writes the supplied data to a file.
	 */
	public static function put(string $path, mixed $data, bool $lock = false): false|string
	{
		return file_put_contents($path, $data, $lock ? LOCK_EX : 0);
	}

	/**
	 * Prepends the supplied data to a file.
	 */
	public static function prepend(string $path, mixed $data, bool $lock = false): false|string
	{
		return file_put_contents($path, $data . file_get_contents($path), $lock ? LOCK_EX : 0);
	}

	/**
	 * Appends the supplied data to a file.
	 */
	public static function append(string $path, mixed $data, bool $lock = false): false|string
	{
		return file_put_contents($path, $data, $lock ? FILE_APPEND | LOCK_EX : FILE_APPEND);
	}

	/**
	 * Truncates a file.
	 */
	public static function truncate(string $path, bool $lock = false): bool
	{
		return (0 === file_put_contents($path, null, $lock ? LOCK_EX : 0));
	}

	/**
	 *  Creates a directory.
	 */
	public function createDirectory(string $path, int $mode = 0777, bool $recursive = false): bool
	{
		return mkdir($path, $mode, $recursive);
	}

	/**
	 * Returns the total size of a filesystem or disk partition in bytes.
	 */
	public function getDiskSize(?string $path = null): float
	{
		return disk_total_space($path ?? (getcwd() ?: __DIR__));
	}

	/**
	 * Returns the total number of available bytes on the filesystem or disk partition.
	 */
	public function getFreeSpaceOnDisk(?string $path = null): float
	{
		return disk_free_space($path ?? (getcwd() ?: __DIR__));
	}

	/**
	 * Includes a file.
	 */
	public function include(string $path): mixed
	{
		return include $path;
	}

	/**
	 * Includes a file it hasn't already been included.
	 */
	public function includeOnce(string $path): mixed
	{
		return include_once $path;
	}

	/**
	 * Requires a file.
	 */
	public function require(string $path): mixed
	{
		return require $path;
	}

	/**
	 * Requires a file if it hasn't already been required.
	 */
	public function requireOnce(string $path): mixed
	{
		return require_once $path;
	}

	/**
	 * Returns a FileInfo object.
	 */
	public function info(string $path): FileInfo
	{
		return new FileInfo($path);
	}

	/**
	 * Returns a SplFileObject.
	 *
	 * @return \SplFileObject
	 */
	public function file(string $path, string $openMode = 'r', bool $useIncludePath = false)
	{
		return new SplFileObject($path, $openMode, $useIncludePath);
	}
}
