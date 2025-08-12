<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\file;

use SensitiveParameter;
use SplFileInfo;

use function finfo_file;
use function finfo_open;
use function hash_equals;
use function hash_file;
use function hash_hmac_file;

/**
 * File info.
 */
class FileInfo extends SplFileInfo
{
	/**
	 * Returns the MIME type of the file.
	 */
	public function getMimeType(): ?string
	{
		$info = finfo_open(FILEINFO_MIME_TYPE);

		$mime = finfo_file($info, $this->getPathname());

		return $mime ?: null;
	}

	/**
	 * Returns the MIME encoding of the file.
	 */
	public function getMimeEncoding(): ?string
	{
		$info = finfo_open(FILEINFO_MIME_ENCODING);

		$encoding = finfo_file($info, $this->getPathname());

		return $encoding ?: null;
	}

	/**
	 * Generates a hash using the contents of the file.
	 */
	public function getHash(string $algorithm = 'sha256', bool $raw = false): string
	{
		return hash_file($algorithm, $this->getPathname(), $raw);
	}

	/**
	 * Returns TRUE if the file matches the provided hash and FALSE if not.
	 */
	public function validateHash(string $hash, string $algorithm = 'sha256', bool $raw = false): bool
	{
		return hash_equals($hash, $this->getHash($algorithm, $raw));
	}

	/**
	 * Generates a HMAC using the contents of the file.
	 */
	public function getHmac(#[SensitiveParameter] string $key, string $algorithm = 'sha256', bool $raw = false): string
	{
		return hash_hmac_file($algorithm, $this->getPathname(), $key, $raw);
	}

	/**
	 * Returns TRUE if the file matches the provided HMAC and FALSE if not.
	 */
	public function validateHmac(string $hmac, #[SensitiveParameter] string $key, string $algorithm = 'sha256', bool $raw = false): bool
	{
		return hash_equals($hmac, $this->getHmac($key, $algorithm, $raw));
	}

	/**
	 * Returns the file permissions.
	 */
	public function getPermissions(): Permissions
	{
		return Permissions::fromInt($this->getPerms() & Permission::FULL->value);
	}

	/**
	 * Returns TRUE if the file permissions contain the specified permissions and FALSE if not.
	 */
	public function hasPermissions(int|Permissions $permissions): bool
	{
		$permissions = $permissions instanceof Permissions ? $permissions : Permissions::fromInt($permissions);

		return $this->getPermissions()->hasPermissions(...$permissions->getPermissions());
	}
}
