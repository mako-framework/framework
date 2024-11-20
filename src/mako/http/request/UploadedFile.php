<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\request;

use mako\file\FileInfo;
use mako\http\request\exceptions\UploadException;

use function is_uploaded_file;
use function move_uploaded_file;

/**
 * Uploaded file.
 */
class UploadedFile extends FileInfo
{
	/**
	 * Constuctor.
	 */
	public function __construct(
		string $path,
		protected string $filename,
		protected int $size,
		protected string $type,
		protected int $errorCode
	) {
		parent::__construct($path);
	}

	/**
	 * Returns the filename reported by the client.
	 */
	public function getReportedFilename(): string
	{
		return $this->filename;
	}

	/**
	 * Returns the size reported by the client in bytes.
	 */
	public function getReportedSize(): int
	{
		return $this->size;
	}

	/**
	 * Returns the mime type reported by the client.
	 */
	public function getReportedMimeType(): string
	{
		return $this->type;
	}

	/**
	 * Does the file have an error?
	 */
	public function hasError(): bool
	{
		return $this->errorCode !== UPLOAD_ERR_OK;
	}

	/**
	 * Returns the file error code.
	 */
	public function getErrorCode(): int
	{
		return $this->errorCode;
	}

	/**
	 * Returns a human friendly error message.
	 */
	public function getErrorMessage(): string
	{
		return match ($this->errorCode) {
			UPLOAD_ERR_OK         => 'There is no error, the file was successfully uploaded.',
			UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
			UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
			UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
			UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
			UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
			UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
			UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
			default               => 'Unknown upload error.',
		};
	}

	/**
	 * Returns TRUE if the file has been uploaded and FALSE if not.
	 */
	public function isUploaded(): bool
	{
		return is_uploaded_file($this->getPathname());
	}

	/**
	 * Moves the file to the desired path.
	 */
	protected function moveUploadedFile(string $path): bool
	{
		return move_uploaded_file($this->getPathname(), $path);
	}

	/**
	 * Moves the file to the desired path.
	 */
	public function moveTo(string $path): bool
	{
		if ($this->hasError()) {
			throw new UploadException($this->getErrorMessage(), $this->getErrorCode());
		}

		if ($this->isUploaded() === false) {
			throw new UploadException('The file that you\'re trying to move was not uploaded.', -1);
		}

		return $this->moveUploadedFile($path);
	}
}
