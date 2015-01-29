<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\http\responses;

use RuntimeException;

use mako\file\FileSystem;
use mako\http\Request;
use mako\http\Response;
use mako\http\responses\ResponseContainerInterface;

/**
 * File response.
 *
 * @author  Frederic G. Østby
 */

class File implements ResponseContainerInterface
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
	 * Options.
	 *
	 * @var array
	 */

	protected $options;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   string  $file     File path
	 * @param   array   $options  Options
	 */

	public function __construct($file, array $options = [])
	{
		$this->fileSystem = $this->getFileSystem();

		if($this->fileSystem->exists($file) === false || $this->fileSystem->isReadable($file) === false)
		{
			throw new RuntimeException(vsprintf("%s(): File [ %s ] is not readable.", [__METHOD__, $file]));
		}

		$this->filePath = $file;

		$this->fileSize = $this->fileSystem->size($file);

		$this->options = $options +
		[
			'file_name'    => basename($file),
			'disposition'  => 'attachment',
			'content_type' => $this->fileSystem->mime($file) ?: 'application/octet-stream',
			'callback'     => null,
		];
	}

	/**
	 * Returns a file system instance.
	 *
	 * @access  protected
	 * @return  \mako\file\FileSystem
	 */

	protected function getFileSystem()
	{
		return new FileSystem;
	}

	/**
	 * Calculates the content range that should be served.
	 *
	 * @access  protected
	 * @param   string       $range  Request range
	 * @return  array|false
	 */

	protected function calculateRange($range)
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

		return compact('start', 'end');
	}

	/**
	 * Sends the file.
	 *
	 * @access  protected
	 * @param   int        $start  Starting point
	 * @param   int        $end    Ending point
	 */

	protected function sendFile($start, $end)
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

		$response->type($this->options['content_type']);

		$response->header('accept-ranges', $request->isSafe() ? 'bytes' : 'none');

		$response->header('content-disposition', $this->options['disposition'] . '; filename="' . $this->options['file_name'] . '"');

		// Get the requested byte range

		$range = $request->header('range');

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

				$response->header('content-length', $this->fileSize);
			}
			else
			{
				// Valid range so we'll need to tell the client which range we're sending
				// and set the content-length header value to the length of the byte range

				$response->status(206);

				$response->header('content-range', sprintf('bytes %s-%s/%s', $range['start'], $range['end'], $this->fileSize));

				$response->header('content-length', $range['end'] - $range['start'] + 1);
			}

			// Send headers and the requested byte range

			$response->sendHeaders();

			$this->sendFile($range['start'], $range['end']);

			// Execute callback if there is one

			if(!empty($this->options['callback']))
			{
				$this->options['callback']($this->filePath);
			}
		}
	}
}