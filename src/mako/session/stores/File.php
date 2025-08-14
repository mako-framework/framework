<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\session\stores;

use mako\file\FileSystem;
use Override;
use SensitiveParameter;

use function is_array;
use function serialize;
use function time;
use function unserialize;

/**
 * File store.
 */
class File implements StoreInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected FileSystem $fileSystem,
		protected string $sessionPath,
		protected array|bool $classWhitelist = false
	) {
	}

	/**
	 * Returns the path to the session file.
	 */
	protected function sessionFile(#[SensitiveParameter] string $sessionId): string
	{
		return "{$this->sessionPath}/{$sessionId}";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function write(#[SensitiveParameter] string $sessionId, array $sessionData, int $dataTTL): void
	{
		if ($this->fileSystem->isWritable($this->sessionPath)) {
			$this->fileSystem->put($this->sessionFile($sessionId), serialize($sessionData)) === false ? false : true;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function read(#[SensitiveParameter] string $sessionId): array
	{
		$sessionData = [];

		if ($this->fileSystem->has($this->sessionFile($sessionId)) && $this->fileSystem->isReadable($this->sessionFile($sessionId))) {
			$sessionData = unserialize($this->fileSystem->get($this->sessionFile($sessionId)), ['allowed_classes' => $this->classWhitelist]);
		}

		return $sessionData;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function delete(#[SensitiveParameter] string $sessionId): void
	{
		if ($this->fileSystem->has($this->sessionFile($sessionId)) && $this->fileSystem->isWritable($this->sessionFile($sessionId))) {
			$this->fileSystem->remove($this->sessionFile($sessionId));
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function gc(int $dataTTL): void
	{
		$files = $this->fileSystem->glob("{$this->sessionPath}/*");

		if (is_array($files)) {
			foreach ($files as $file) {
				if (($this->fileSystem->lastModified($file) + $dataTTL) < time() && $this->fileSystem->isWritable($file)) {
					$this->fileSystem->remove($file);
				}
			}
		}
	}
}
