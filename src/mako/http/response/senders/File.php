<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response\senders;

use Closure;
use mako\file\FileSystem;
use mako\http\exceptions\HttpException;
use mako\http\Request;
use mako\http\Response;
use mako\http\response\Status;
use Override;

use function basename;
use function connection_aborted;
use function count;
use function explode;
use function fclose;
use function feof;
use function flush;
use function fopen;
use function fread;
use function fseek;
use function ftell;
use function ob_end_clean;
use function ob_get_level;
use function sprintf;
use function substr;

/**
 * File response.
 */
class File implements ResponseSenderInterface
{
	/**
	 * File size.
	 */
	protected int $fileSize;

	/**
	 * Filename.
	 */
	protected string $filename;

	/**
	 * Content disposition.
	 */
	protected string $disposition;

	/**
	 * Content type.
	 */
	protected string $contentType;

	/**
	 * Callback.
	 */
	protected Closure $callback;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected FileSystem $fileSystem,
		protected string $filePath
	) {
		if ($this->fileSystem->has($this->filePath) === false || $this->fileSystem->isReadable($this->filePath) === false) {
			throw new HttpException(sprintf('File [ %s ] is not readable.', $this->filePath));
		}

		$this->fileSize = $this->fileSystem->size($this->filePath);
	}

	/**
	 * Sets the filename.
	 *
	 * @return $this
	 */
	public function setName(string $name): File
	{
		$this->filename = $name;

		return $this;
	}

	/**
	 * Sets the content disposition.
	 *
	 * @return $this
	 */
	public function setDisposition(string $disposition): File
	{
		$this->disposition = $disposition;

		return $this;
	}

	/**
	 * Sets the response content type.
	 *
	 * @return $this
	 */
	public function setType(string $type): File
	{
		$this->contentType = $type;

		return $this;
	}

	/**
	 * Sets the callback closure.
	 *
	 * @return $this
	 */
	public function done(Closure $callback): File
	{
		$this->callback = $callback;

		return $this;
	}

	/**
	 * Returns the filename.
	 */
	protected function getName(): string
	{
		return $this->filename ?? basename($this->filePath);
	}

	/**
	 * Returns the content disposition.
	 */
	protected function getDisposition(): string
	{
		return $this->disposition ?? 'attachment';
	}

	/**
	 * Returns the content type.
	 */
	protected function getContenType(): string
	{
		return $this->contentType ?? ($this->fileSystem->info($this->filePath)->getMimeType() ?? 'application/octet-stream');
	}

	/**
	 * Calculates the content range that should be served.
	 *
	 * @return array{start: int, end:int}|false
	 */
	protected function calculateRange(string $range): array|false
	{
		// Remove the "range=" part of the header value

		$range = substr($range, 6);

		// Split the range starting and ending points

		$range = explode('-', $range, 2);

		// Check that the range contains two values

		if (count($range) !== 2) {
			return false;
		}

		// Determine start and ending points

		$end = $range[1] === '' ? $this->fileSize - 1 : $range[1];

		if ($range[0] === '') {
			$start = $this->fileSize - $end;
			$end   = $this->fileSize - 1;
		}
		else {
			$start = $range[0];
		}

		$start = (int) $start;
		$end   = (int) $end;

		// Check that the range is satisfiable

		if ($start > $end || $end + 1 > $this->fileSize) {
			return false;
		}

		// Return the range

		return ['start' => $start, 'end' => $end];
	}

	/**
	 * Sends the file.
	 */
	protected function sendFile(int $start, int $end): void
	{
		// Erase output buffers and disable output buffering

		while (ob_get_level() > 0) {
			ob_end_clean();
		}

		// Open the file for reading

		$handle = fopen($this->filePath, 'rb');

		// Move to the correct starting position

		fseek($handle, $start);

		// Send the file contents

		$chunkSize = 4096;

		while (!feof($handle) && ($pos = ftell($handle)) <= $end && !connection_aborted()) {
			if ($pos + $chunkSize > $end) {
				$chunkSize = $end - $pos + 1;
			}

			echo fread($handle, $chunkSize);

			flush();
		}

		fclose($handle);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function send(Request $request, Response $response): void
	{
		// Add headers that should always be included

		$response->setType($this->getContenType());

		$response->headers->add('Accept-Ranges', $request->isSafe() ? 'bytes' : 'none');

		$response->headers->add('Content-Disposition', "{$this->getDisposition()}; filename=\"{$this->getName()}\"");

		// Get the requested byte range

		$range = $request->headers->get('range');

		if ($range !== null) {
			$range = $this->calculateRange($range);
		}

		if ($range === false) {
			// Not an acceptable range so we'll just send an empty response
			// along with a "requested range not satisfiable" status

			$response->setStatus(Status::RANGE_NOT_SATISFIABLE);

			$response->sendHeaders();
		}
		else {
			if ($range === null) {
				// No range was provided by the client so we'll just fake one for the sendFile method
				// and set the content-length header value to the full file size

				$range = ['start' => 0, 'end' => $this->fileSize - 1];

				$response->headers->add('Content-Length', (string) $this->fileSize);
			}
			else {
				// Valid range so we'll need to tell the client which range we're sending
				// and set the content-length header value to the length of the byte range

				$response->setStatus(Status::PARTIAL_CONTENT);

				$response->headers->add('Content-Range', sprintf('bytes %s-%s/%s', $range['start'], $range['end'], $this->fileSize));

				$response->headers->add('Content-Length', (string) ($range['end'] - $range['start'] + 1));
			}

			// Send headers and the requested byte range

			$response->sendHeaders();

			$this->sendFile($range['start'], $range['end']);

			// Execute callback if there is one

			if (!empty($this->callback)) {
				$callback = $this->callback;

				$callback($this->filePath);
			}
		}
	}
}
