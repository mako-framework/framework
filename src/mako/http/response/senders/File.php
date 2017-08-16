<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response\senders;

use Closure;
use RuntimeException;

use mako\file\FileSystem;
use mako\http\Request;
use mako\http\Response;
use mako\http\response\senders\ResponseSenderInterface;

/**
 * File response.
 *
 * @author Frederic G. Østby
 */
class File implements ResponseSenderInterface
{
	/**
	 * File system instance.
	 *
	 * @var \mako\file\FileSystem
	 */
	protected $fileSystem;

	/**
	 * File path.
	 *
	 * @var string
	 */
	protected $filePath;

	/**
	 * File size.
	 *
	 * @var int
	 */
	protected $fileSize;

	/**
	 * File name.
	 *
	 * @var string
	 */
	protected $fileName;

	/**
	 * Content disposition
	 *
	 * @var string
	 */
	protected $disposition;

	/**
	 * Content type.
	 *
	 * @var string
	 */
	protected $contentType;

	/**
	 * Callback.
	 *
	 * @var \Closure
	 */
	protected $callback;

	/**
	 * Constructor.
	 *
	 * @param \mako\file\FileSystem $fileSystem FileSytem instance
	 * @param string                $file       File path
	 */
	public function __construct(FileSystem $fileSystem, string $file)
	{
		$this->fileSystem = $fileSystem;

		if($this->fileSystem->has($file) === false || $this->fileSystem->isReadable($file) === false)
		{
			throw new RuntimeException(vsprintf("%s(): File [ %s ] is not readable.", [__METHOD__, $file]));
		}

		$this->filePath = $file;

		$this->fileSize = $this->fileSystem->size($file);
	}

	/**
	 * Sets the file name.
	 *
	 * @param  string                           $name File name
	 * @return \mako\http\response\senders\File
	 */
	public function name(string $name): File
	{
		$this->fileName = $name;

		return $this;
	}

	/**
	 * Sets the content disposition.
	 *
	 * @param  string                           $disposition Content disposition
	 * @return \mako\http\response\senders\File
	 */
	public function disposition(string $disposition): File
	{
		$this->disposition = $disposition;

		return $this;
	}

	/**
	 * Sets the content type.
	 *
	 * @param  string                           $type Mime type
	 * @return \mako\http\response\senders\File
	 */
	public function type(string $type): File
	{
		$this->contentType = $type;

		return $this;
	}

	/**
	 * Sets the callback closure.
	 *
	 * @param  \Closure                         $callback Callback closure
	 * @return \mako\http\response\senders\File
	 */
	public function done(Closure $callback): File
	{
		$this->callback = $callback;

		return $this;
	}

	/**
	 * Returns the file name.
	 *
	 * @return string
	 */
	protected function getName(): string
	{
		return $this->fileName ?? basename($this->filePath);
	}

	/**
	 * Returns the content disposition.
	 *
	 * @return string
	 */
	protected function getDisposition(): string
	{
		return $this->disposition ?? 'attachment';
	}

	/**
	 * Returns the content type.
	 *
	 * @return string
	 */
	protected function getContenType(): string
	{
		return $this->contentType ?? ($this->fileSystem->mime($this->filePath) ?: 'application/octet-stream');
	}

	/**
	 * Calculates the content range that should be served.
	 *
	 * @param  string      $range Request range
	 * @return array|false
	 */
	protected function calculateRange(string $range)
	{
		// Remove the "range=" part of the header value

		$range = substr($range, 6);

		// Split the range starting and ending points

		$range = explode('-', $range, 2);

		// Check that the range contains two values

		if(count($range) !== 2)
		{
			return false;
		}

		// Determine start and ending points

		$end = $range[1] === '' ? $this->fileSize - 1 : $range[1];

		if($range[0] === '')
		{
			$start = $this->fileSize - $end;
			$end   = $this->fileSize - 1;
		}
		else
		{
			$start = $range[0];
		}

		$start = (int) $start;
		$end   = (int) $end;

		// Check that the range is satisfiable

		if($start > $end || $end + 1 > $this->fileSize)
		{
			return false;
		}

		// Return the range

		return ['start' => $start, 'end' => $end];
	}

	/**
	 * Sends the file.
	 *
	 * @param int $start Starting point
	 * @param int $end   Ending point
	 */
	protected function sendFile(int $start, int $end)
	{
		// Erase output buffers and disable output buffering

		while(ob_get_level() > 0) ob_end_clean();

		// Open the file for reading

		$handle = fopen($this->filePath, 'rb');

		// Move to the correct starting position

		fseek($handle, $start);

		// Send the file contents

		$chunkSize = 4096;

		while(!feof($handle) && ($pos = ftell($handle)) <= $end && !connection_aborted())
		{
			if($pos + $chunkSize > $end)
			{
				$chunkSize = $end - $pos + 1;
			}

			echo fread($handle, $chunkSize);

			flush();
		}

		fclose($handle);
	}

	/**
	 * {@inheritdoc}
	 */
	public function send(Request $request, Response $response)
	{
		// Add headers that should always be included

		$response->type($this->getContenType());

		$response->header('Accept-Ranges', $request->isSafe() ? 'bytes' : 'none');

		$response->header('Content-Disposition', $this->getDisposition() . '; filename="' . $this->getName() . '"');

		// Get the requested byte range

		$range = $request->headers->get('range');

		if($range !== null)
		{
			$range = $this->calculateRange($range);
		}

		if($range === false)
		{
			// Not an acceptable range so we'll just send an empty response
			// along with a "requested range not satisfiable" status

			$response->status(416);

			$response->sendHeaders();
		}
		else
		{
			if($range === null)
			{
				// No range was provided by the client so we'll just fake one for the sendFile method
				// and set the content-length header value to the full file size

				$range = ['start' => 0, 'end' => $this->fileSize - 1];

				$response->header('Content-Length', $this->fileSize);
			}
			else
			{
				// Valid range so we'll need to tell the client which range we're sending
				// and set the content-length header value to the length of the byte range

				$response->status(206);

				$response->header('Content-Range', sprintf('bytes %s-%s/%s', $range['start'], $range['end'], $this->fileSize));

				$response->header('Content-Length', $range['end'] - $range['start'] + 1);
			}

			// Send headers and the requested byte range

			$response->sendHeaders();

			$this->sendFile($range['start'], $range['end']);

			// Execute callback if there is one

			if(!empty($this->callback))
			{
				$callback = $this->callback;

				$callback($this->filePath);
			}
		}
	}
}
