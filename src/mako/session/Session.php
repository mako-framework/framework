<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\session;

use mako\http\Request;
use mako\http\Response;
use mako\session\stores\StoreInterface;
use RuntimeException;

use function array_flip;
use function array_intersect_key;
use function array_merge;
use function array_replace_recursive;
use function array_slice;
use function array_unshift;
use function hash;
use function hash_equals;
use function mt_rand;
use function random_bytes;

/**
 * Session class.
 *
 * @author Frederic G. Østby
 */
class Session
{
	/**
	 * Maximum number of tokens stored per session.
	 *
	 * @var int
	 */
	const MAX_TOKENS = 20;

	/**
	 * Has the session been destroyed?
	 *
	 * @var bool
	 */
	protected $destroyed = false;

	/**
	 * Request.
	 *
	 * @var \mako\http\Request
	 */
	protected $request;

	/**
	 * Response.
	 *
	 * @var \mako\http\Response
	 */
	protected $response;

	/**
	 * Session store.
	 *
	 * @var \mako\session\stores\StoreInterface
	 */
	protected $store;

	/**
	 * Should the session data be commited automatically?
	 *
	 * @var bool
	 */
	protected $autoCommit;

	/**
	 * Session options.
	 *
	 * @var array
	 */
	protected $options =
	[
		'name'           => 'mako_session',
		'data_ttl'       => 1800,
		'cookie_ttl'     => 0,
		'cookie_options' =>
		[
			'path'     => '/',
			'domain'   => '',
			'secure'   => false,
			'httponly' => true,
		],
	];

	/**
	 * Session id.
	 *
	 * @var string
	 */
	protected $sessionId;

	/**
	 * Session data.
	 *
	 * @var array
	 */
	protected $sessionData = [];

	/**
	 * Flashdata.
	 *
	 * @var array
	 */
	protected $flashData = [];

	/**
	 * Session token.
	 *
	 * @var string
	 */
	protected $token;

	/**
	 * Constructor.
	 *
	 * @param \mako\http\Request                  $request    Request instance
	 * @param \mako\http\Response                 $response   Response instance
	 * @param \mako\session\stores\StoreInterface $store      Session store instance
	 * @param array                               $options    Session options
	 * @param bool                                $autoCommit Should the session data be commited automatically?
	 */
	public function __construct(Request $request, Response $response, StoreInterface $store, array $options = [], $autoCommit = true)
	{
		$this->request = $request;

		$this->response = $response;

		$this->store = $store;

		$this->autoCommit = $autoCommit;

		$this->options = array_replace_recursive($this->options, $options);

		$this->gc();

		$this->start();
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		if($this->autoCommit)
		{
			$this->commit();
		}
	}

	/**
	 * Starts the session.
	 */
	protected function start(): void
	{
		// Get the session id from the cookie or generate a new one if it doesn't exist.

		$this->sessionId = $this->request->getCookies()->getSigned($this->options['name'], false);

		if($this->sessionId === false)
		{
			$this->sessionId = $this->generateId();
		}

		// Create a new / update the existing session cookie

		$this->setCookie();

		// Load the session data

		$this->loadData();

		// Create a session token if we don't have one

		if(empty($this->sessionData['mako.token']))
		{
			$this->sessionData['mako.token'] = $this->generateId();
		}

		$this->token = $this->sessionData['mako.token'];
	}

	/**
	 * Writes data to session store.
	 */
	public function commit(): void
	{
		// Replace old flash data with new

		$this->sessionData['mako.flashdata'] = $this->flashData;

		// Write session data

		if(!$this->destroyed)
		{
			$this->store->write($this->sessionId, $this->sessionData, $this->options['data_ttl']);
		}
	}

	/**
	 * Calls the session store garbage collector.
	 */
	protected function gc(): void
	{
		if(mt_rand(1, 100) === 100)
		{
			$this->store->gc($this->options['data_ttl']);
		}
	}

	/**
	 * Generates a session id.
	 *
	 * @return string
	 */
	protected function generateId(): string
	{
		return hash('sha256', random_bytes(16));
	}

	/**
	 * Adds a session cookie to the response.
	 */
	protected function setCookie(): void
	{
		if($this->options['cookie_options']['secure'] && !$this->request->isSecure())
		{
			throw new RuntimeException('Attempted to set a secure cookie over a non-secure connection.');
		}

		$this->response->getCookies()->addSigned($this->options['name'], $this->sessionId, $this->options['cookie_ttl'], $this->options['cookie_options']);
	}

	/**
	 * Loads the session data.
	 */
	protected function loadData(): void
	{
		$this->sessionData = $this->store->read($this->sessionId);
	}

	/**
	 * Returns the session id.
	 *
	 * @return string
	 */
	public function getId(): string
	{
		return $this->sessionId;
	}

	/**
	 * Regenerate the session id and returns it.
	 *
	 * @param  bool   $keepOld Keep the session data associated with the old session id?
	 * @return string
	 */
	public function regenerateId(bool $keepOld = false): string
	{
		// Delete old data if we don't want to keep it

		if(!$keepOld)
		{
			$this->store->delete($this->sessionId);
		}

		// Generate a new id and set a new cookie

		$this->sessionId = $this->generateId();

		$this->setCookie();

		// Return the new session id

		return $this->sessionId;
	}

	/**
	 * Returns all the seesion data.
	 *
	 * @return array
	 */
	public function getData(): array
	{
		return $this->sessionData;
	}

	/**
	 * Store a value in the session.
	 *
	 * @param string $key   Session key
	 * @param mixed  $value Session data
	 */
	public function put(string $key, $value): void
	{
		$this->sessionData[$key] = $value;
	}

	/**
	 * Returns TRUE if key exists in the session and FALSE if not.
	 *
	 * @param  string $key Session key
	 * @return bool
	 */
	public function has(string $key): bool
	{
		return isset($this->sessionData[$key]);
	}

	/**
	 * Returns a value from the session.
	 *
	 * @param  string $key     Session key
	 * @param  mixed  $default Default value
	 * @return mixed
	 */
	public function get(string $key, $default = null)
	{
		return $this->sessionData[$key] ?? $default;
	}

	/**
	 * Gets a value from the session and replaces it.
	 *
	 * @param  string $key     Session key
	 * @param  mixed  $value   Session data
	 * @param  mixed  $default Default value
	 * @return mixed
	 */
	public function getAndPut(string $key, $value, $default = null)
	{
		$storedValue = $this->get($key, $default);

		$this->put($key, $value);

		return $storedValue;
	}

	/**
	 * Gets a value from the session and removes it.
	 *
	 * @param  string $key     Session key
	 * @param  mixed  $default Default value
	 * @return mixed
	 */
	public function getAndRemove(string $key, $default = null)
	{
		$storedValue = $this->get($key, $default);

		$this->remove($key);

		return $storedValue;
	}

	/**
	 * Removes a value from the session.
	 *
	 * @param string $key Session key
	 */
	public function remove(string $key): void
	{
		unset($this->sessionData[$key]);
	}

	/**
	 * Store a flash value in the session.
	 *
	 * @param  string $key   Flash key
	 * @param  mixed  $value Flash data
	 * @return mixed
	 */
	public function putFlash(string $key, $value)
	{
		$this->flashData[$key] = $value;
	}

	/**
	 * Returns TRUE if key exists in the session and FALSE if not.
	 *
	 * @param  string $key Session key
	 * @return bool
	 */
	public function hasFlash(string $key): bool
	{
		return isset($this->sessionData['mako.flashdata'][$key]);
	}

	/**
	 * Returns a flash value from the session.
	 *
	 * @param  string $key     Session key
	 * @param  mixed  $default Default value
	 * @return mixed
	 */
	public function getFlash(string $key, $default = null)
	{
		return $this->sessionData['mako.flashdata'][$key] ?? $default;
	}

	/**
	 * Removes a value from the session.
	 *
	 * @param string $key Session key
	 */
	public function removeFlash(string $key): void
	{
		unset($this->sessionData['mako.flashdata'][$key]);
	}

	/**
	 * Extends the lifetime of the flash data by one request.
	 *
	 * @param array $keys Keys to preserve
	 */
	public function reflash(array $keys = []): void
	{
		$flashData = $this->sessionData['mako.flashdata'] ?? [];

		$flashData = empty($keys) ? $flashData : array_intersect_key($flashData, array_flip($keys));

		$this->flashData = array_merge($this->flashData, $flashData);
	}

	/**
	 * Returns the session token.
	 *
	 * @return string
	 */
	public function getToken(): string
	{
		return $this->token;
	}

	/**
	 * Generates a new session token and returns it.
	 *
	 * @return string
	 */
	public function regenerateToken(): string
	{
		return $this->token = $this->sessionData['mako.token'] = $this->generateId();
	}

	/**
	 * Validates the provided token.
	 *
	 * @param  string $token Token to validate
	 * @return bool
	 */
	public function validateToken(string $token): bool
	{
		return hash_equals($this->token, $token);
	}

	/**
	 * Returns random security token.
	 *
	 * @return string
	 */
	public function generateOneTimeToken(): string
	{
		if(!empty($this->sessionData['mako.tokens']))
		{
			$this->sessionData['mako.tokens'] = array_slice($this->sessionData['mako.tokens'], 0, (static::MAX_TOKENS - 1));
		}
		else
		{
			$this->sessionData['mako.tokens'] = [];
		}

		$token = $this->generateId();

		array_unshift($this->sessionData['mako.tokens'], $token);

		return $token;
	}

	/**
	 * Validates security token.
	 *
	 * @param  string $token Security token
	 * @return bool
	 */
	public function validateOneTimeToken(string $token): bool
	{
		if(!empty($this->sessionData['mako.tokens']))
		{
			foreach($this->sessionData['mako.tokens'] as $key => $value)
			{
				if(hash_equals($value, $token))
				{
					unset($this->sessionData['mako.tokens'][$key]);

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Clears all session data.
	 */
	public function clear(): void
	{
		$this->sessionData = [];
	}

	/**
	 * Destroys the session.
	 */
	public function destroy(): void
	{
		$this->store->delete($this->sessionId);

		$this->response->getCookies()->delete($this->options['name'], $this->options['cookie_options']);

		$this->destroyed = true;
	}
}
