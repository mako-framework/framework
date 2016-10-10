<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http;

use SplFileInfo;

use mako\http\exceptions\UploadException;

/**
 * Uploaded file.
 *
 * @author  Frederic G. Østby
 */
class UploadedFile extends SplFileInfo
{
	/**
	 * File name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * File size.
	 *
	 * @var int
	 */
	protected $size;

	/**
	 * File mime type.
	 *
	 * @var int
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
	 * @access  public
	 * @param   string  $path       File path
	 * @param   string  $name       File name
	 * @param   int     $size       File size
	 * @param   string  $type       File mime type
	 * @param   int     $errorCode  File error code
	 */
	public function __construct(string $path, string $name, int $size, string $type, int $errorCode)
	{
		parent::__construct($path);

		$this->name = $name;

		$this->size = $size;

		$this->type = $type;

		$this->errorCode = $errorCode;
	}

	/**
	 * Returns the file name.
	 *
	 * @access  public
	 * @return  string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Returns the size reported by the client in bytes.
	 *
	 * @access  public
	 * @return  int
	 */
	public function getReportedSize(): int
	{
		return $this->size;
	}

	/**
	 * Returns the mime type reported by the client.
	 *
	 * @access  public
	 * @return  string
	 */
	public function getReportedType(): string
	{
		return $this->type;
	}

	/**
	 * Does the file have an error?
	 *
	 * @access  public
	 * @return  bool
	 */
	public function hasError(): bool
	{
		return $this->errorCode !== UPLOAD_ERR_OK;
	}

	/**
	 * Returns the file error code.
	 *
	 * @access  public
	 * @return  int
	 */
	public function getErrorCode(): int
	{
		return $this->errorCode;
	}

	/**
	 * Returns a human friendly error message.
	 *
	 * @access  public
	 * @return  string
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
	 * @access  public
	 * @return  bool
	 */
	public function isUploaded(): bool
	{
		return is_uploaded_file($this->getPathname());
	}

	/**
	 * Moves the file to the desired path.
	 *
	 * @access  protected
	 * @param   string     $path  Storage path
	 * @return  bool
	 */
	protected function moveUploadedFile(string $path): bool
	{
		return move_uploaded_file($this->getPathname(), $path);
	}

	/**
	 * Moves the file to the desired path.
	 *
	 * @access  public
	 * @param   string  $path  Storage path
	 * @return  bool
	 */
	public function moveTo(string $path): bool
	{
		if($this->hasError())
		{
			throw new UploadException(vsprintf("%s(): %s", [__METHOD__, $this->getErrorMessage()]), $this->getErrorCode());
		}

		if($this->isUploaded() === false)
		{
			throw new UploadException(vsprintf("%s(): The file that you're trying to move was not uploaded.", [__METHOD__,]), -1);
		}

		return $this->moveUploadedFile($path);
	}
}