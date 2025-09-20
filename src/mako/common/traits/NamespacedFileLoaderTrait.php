<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\common\traits;

use mako\common\traits\exceptions\NamespacedFileLoaderTraitException;

use function array_unshift;
use function explode;
use function sprintf;
use function str_contains;
use function str_replace;

/**
 * Namespaced file loader trait.
 */
trait NamespacedFileLoaderTrait
{
	/**
	 * Default path.
	 */
	protected string $path = '';

	/**
	 * File extension.
	 */
	protected string $extension = '.php';

	/**
	 * Namespaces.
	 */
	protected array $namespaces = [];

	/**
	 * Sets the default path.
	 */
	public function setPath(string $path): void
	{
		$this->path = $path;
	}

	/**
	 * Sets the extension.
	 */
	public function setExtension(string $extension): void
	{
		$this->extension = $extension;
	}

	/**
	 * Registers a namespace.
	 */
	public function registerNamespace(string $namespace, string $path): void
	{
		$this->namespaces[$namespace] = $path;
	}

	/**
	 * Returns the path to the file.
	 */
	protected function getFilePath(string $file, ?string $extension = null, ?string $suffix = null): string
	{
		if (str_contains($file, '::') === false) {
			// No namespace so we'll just use the default path

			$path = $this->path;
		}
		else {
			// The file is namespaced so we'll use the namespace path

			[$namespace, $file] = explode('::', $file, 2);

			if (!isset($this->namespaces[$namespace])) {
				throw new NamespacedFileLoaderTraitException(sprintf('The [ %s ] namespace does not exist.', $namespace));
			}

			$path = $this->namespaces[$namespace];
		}

		// Append suffix to path if needed

		if ($suffix !== null) {
			$path .= DIRECTORY_SEPARATOR . $suffix;
		}

		// Return full path to file

		return $path . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $file) . ($extension ?? $this->extension);
	}

	/**
	 * Returns an array of cascading file paths.
	 */
	protected function getCascadingFilePaths(string $file, ?string $extension = null, ?string $suffix = null): array
	{
		$paths = [];

		if (str_contains($file, '::') === false) {
			// No namespace so we'll just have add a single file

			$paths[] = $this->getFilePath($file, $extension, $suffix);
		}
		else {
			// Add the namespaced file first

			$paths[] = $this->getFilePath($file, $extension, $suffix);

			// Prepend the cascading file

			[$package, $file] = explode('::', $file);

			$suffix = 'packages' . DIRECTORY_SEPARATOR . $package . (($suffix !== null) ? DIRECTORY_SEPARATOR . $suffix : '');

			array_unshift($paths, $this->getFilePath($file, $extension, $suffix));
		}

		return $paths;
	}
}
