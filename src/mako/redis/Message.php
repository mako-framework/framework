<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis;

use mako\redis\exceptions\RedisException;
use Override;
use Stringable;

use function sprintf;

/**
 * Redis pub/sub message.
 */
class Message implements Stringable
{
	/**
	 * Message type.
	 */
	protected string $type;

	/**
	 * Message channel.
	 */
	protected ?string $channel = null;

	/**
	 * Channel pattern.
	 */
	protected ?string $pattern = null;

	/**
	 * Message body.
	 */
	protected ?string $body = null;

	/**
	 * Constructor.
	 */
	public function __construct(array $response)
	{
		$this->parseResponse($response);
	}

	/**
	 * Parses the message response.
	 */
	protected function parseResponse(array $response): void
	{
		switch ($response[0]) {
			case 'message':
			case 'subscribe':
			case 'unsubscribe':
				$this->type    = $response[0];
				$this->channel = $response[1];
				$this->body    = $response[2];
				break;
			case 'psubscribe':
			case 'punsubscribe':
				$this->type    = $response[0];
				$this->pattern = $response[1];
				$this->body    = $response[2];
				break;
			case 'pmessage':
				$this->type    = $response[0];
				$this->pattern = $response[1];
				$this->channel = $response[2];
				$this->body    = $response[3];
				break;
			case 'pong':
				$this->type    = $response[0];
				break;
			default:
				throw new RedisException(sprintf('Unable to parse message of type [ %s ].', $response[0]));
		}
	}

	/**
	 * Returns the message type.
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * Returns the message channel.
	 */
	public function getChannel(): ?string
	{
		return $this->channel;
	}

	/**
	 * Returns the channel pattern.
	 */
	public function getPattern(): ?string
	{
		return $this->pattern;
	}

	/**
	 * Returns the message body.
	 */
	public function getBody(): ?string
	{
		return $this->body;
	}

	/**
	 * Returns the message body.
	 */
	#[Override]
	public function __toString(): string
	{
		return (string) $this->body;
	}
}
