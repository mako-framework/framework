<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response\senders;

use Closure;
use Generator;
use JsonSerializable;
use mako\http\Request;
use mako\http\Response;
use mako\http\response\senders\stream\event\Event;
use mako\http\response\senders\stream\StreamTrait;
use Override;
use Stringable;

use function connection_aborted;
use function is_object;
use function json_encode;

/**
 * Event stream response.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/API/Server-sent_events/Using_server-sent_events
 */
class EventStream implements ResponseSenderInterface
{
	use StreamTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Closure $stream
	) {
	}

	/**
	 * Stringifies the value.
	 */
	protected function stringifyValue(null|float|int|JsonSerializable|string|Stringable $value): string
	{
		if (is_object($value)) {
			if ($value instanceof JsonSerializable) {
				return json_encode($value, JSON_THROW_ON_ERROR);
			}
		}

		return (string) $value;
	}

	/**
	 * Prepares the event for sending.
	 */
	protected function prepareEvent(Event $event): string
	{
		$output = '';

		foreach ($event->fields as $field) {
			$output .= "{$field->type->value}: {$this->stringifyValue($field->value)}\n";
		}

		$output .= "\n";

		return $output;
	}

	/**
	 * Sends the stream to the client.
	 */
	protected function sendStream(): void
	{
		foreach ((fn (): Generator => ($this->stream)())() as $event) {
			 if (connection_aborted()) {
				break;
			 }

			 $this->sendChunk($this->prepareEvent($event));
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
    public function send(Request $request, Response $response): void
    {
		$response->setType('text/event-stream', 'UTF-8');

		$response->headers->add('Connection', 'keep-alive');
		$response->headers->add('Cache-Control', 'no-cache');
		$response->headers->add('X-Accel-Buffering', 'no');

		// Erase output buffers and disable output buffering

		$this->eraseAndDisableOutputBuffers();

		// Send headers

		$response->sendHeaders();

		// Send the stream

		$this->sendStream();
    }
}
