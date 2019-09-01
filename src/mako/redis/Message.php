<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis;

/**
 * Redis pub/sub message.
 *
 * @author Frederic G. Østby
 */
class Message
{
	/**
	 * Message type.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Message channel.
	 *
	 * @var string|null
	 */
	protected $channel = null;

	/**
	 * Channel pattern.
	 *
	 * @var string|null
	 */
	protected $pattern = null;

	/**
	 * Message body.
	 *
	 * @var string|null
	 */
	protected $body = null;

	/**
	 * Constructor.
	 *
	 * @param array $response Response
	 */
	public function __construct(array $response)
	{
		$this->parseResponse($response);
	}

	/**
	 * Parses the message response.
	 *
	 * @param array $response Response
	 */
	protected function parseResponse(array $response): void
	{
		switch($response[0])
		{
			case 'message':
			case 'subscribe':
			case 'unsubscribe':
			case 'psubscribe':
			case 'punsubscribe':
				$this->type    = $response[0];
				$this->channel = $response[1];
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
				throw new RedisException(vsprintf('Unable to parse message of type [ %s ].', $response[0]));
		}
	}

	/**
	 * Returns the message type.
	 *
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * Returns the message channel.
	 *
	 * @return string|null
	 */
	public function getChannel(): ?string
	{
		return $this->channel;
	}

	/**
	 * Returns the channel pattern.
	 *
	 * @return string|null
	 */
	public function getPattern(): ?string
	{
		return $this->pattern;
	}

	/**
	 * Returns the message body.
	 *
	 * @return string|null
	 */
	public function getBody(): ?string
	{
		return $this->body;
	}

	/**
	 * Returns the message body.
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return (string) $this->body;
	}
}
