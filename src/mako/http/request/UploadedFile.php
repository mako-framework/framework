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
use function vsprintf;

/**
 * Uploaded file.
 */
class UploadedFile extends FileInfo
{
	/**
	 * Filename.
	 *
	 * @var string
	 */
	protected $filename;

	/**
	 * File size.
	 *
	 * @var int
	 */
	protected $size;

	/**
	 * File mime type.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * File error code.
	 *
	 * @var int
	 */
	protected $errorCode;

	/**
	 * Constuctor.
	 *
	 * @param string $path      File path
	 * @param string $name      Filename
	 * @param int    $size      File size
	 * @param string $type      File mime type
	 * @param int    $errorCode File error code
	 */
	public function __construct(string $path, string $name, int $size, string $type, int $errorCode)
	{
		parent::__construct($path);

		$this->filename = $name;

		$this->size = $size;

		$this->type = $type;

		$this->errorCode = $errorCode;
	}

	/**
	 * Returns the filename reported by the client.
	 *
	 * @return string
	 */
	public function getReportedFilename(): string
	{
		return $this->filename;
	}

	/**
	 * Returns the size reported by the client in bytes.
	 *
	 * @return int
	 */
	public function getReportedSize(): int
	{
		return $this->size;
	}

	/**
	 * Returns the mime type reported by the client.
	 *
	 * @return string
	 */
	public function getReportedMimeType(): string
	{
		return $this->type;
	}

	/**
	 * Does the file have an error?
	 *
	 * @return bool
	 */
	public function hasError(): bool
	{
		return $this->errorCode !== UPLOAD_ERR_OK;
	}

	/**
	 * Returns the file error code.
	 *
	 * @return int
	 */
	public function getErrorCode(): int
	{
		return $this->errorCode;
	}

	/**
	 * Returns a human friendly error message.
	 *
	 * @return string
	 */
	public function getErrorMessage(): string
	{
		switch($this->errorCode)
		{
			case UPLOAD_ERR_OK:
				return 'There is no error, the file was successfully uploaded.';
			case UPLOAD_ERR_INI_SIZE:
				return 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
			case UPLOAD_ERR_FORM_SIZE:
				return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
			case UPLOAD_ERR_PARTIAL:
				return 'The uploaded file was only partially uploaded.';
			case UPLOAD_ERR_NO_FILE:
				return 'No file was uploaded.';
			case UPLOAD_ERR_NO_TMP_DIR:
				return 'Missing a temporary folder.';
			case UPLOAD_ERR_CANT_WRITE:
				return 'Failed to write file to disk.';
			case UPLOAD_ERR_EXTENSION:
				return 'A PHP extension stopped the file upload.';
			default:
				return 'Unknown upload error.';
		}
	}

	/**
	 * Returns TRUE if the file has been uploaded and FALSE if not.
	 *
	 * @return bool
	 */
	public function isUploaded(): bool
	{
		return is_uploaded_file($this->getPathname());
	}

	/**
	 * Moves the file to the desired path.
	 *
	 * @param  string $path Storage path
	 * @return bool
	 */
	protected function moveUploadedFile(string $path): bool
	{
		return move_uploaded_file($this->getPathname(), $path);
	}

	/**
	 * Moves the file to the desired path.
	 *
	 * @param  string $path Storage path
	 * @return bool
	 */
	public function moveTo(string $path): bool
	{
		if($this->hasError())
		{
			throw new UploadException(vsprintf('%s', [$this->getErrorMessage()]), $this->getErrorCode());
		}

		if($this->isUploaded() === false)
		{
			throw new UploadException('The file that you\'re trying to move was not uploaded.', -1);
		}

		return $this->moveUploadedFile($path);
	}
}
