<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\session;

use mako\http\Request;
use mako\http\Response;
use mako\session\exceptions\SessionException;
use mako\session\stores\StoreInterface;
use SensitiveParameter;

use function array_flip;
use function array_intersect_key;
use function array_replace_recursive;
use function array_slice;
use function array_unshift;
use function hash;
use function hash_equals;
use function mt_rand;
use function random_bytes;

/**
 * Session class.
 */
class Session
{
	/**
	 * Maximum number of tokens stored per session.
	 */
	protected const int MAX_TOKENS = 20;

	/**
	 * Has the session been destroyed?
	 */
	protected bool $destroyed = false;

	/**
	 * Session options.
	 */
	protected array $options = [
		'name'           => 'mako_session',
		'data_ttl'       => 1800,
		'cookie_ttl'     => 0,
		'cookie_options' => [
			'path'        => '/',
			'domain'      => '',
			'secure'      => false,
			'partitioned' => false,
			'httponly'    => true,
		],
	];

	/**
	 * Session id.
	 */
	protected string $sessionId;

	/**
	 * Session data.
	 */
	protected array $sessionData = [];

	/**
	 * Session token.
	 */
	protected string $token;

	/**
	 * Flashdata.
	 */
	protected array $flashData = [];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Request $request,
		protected Response $response,
		protected StoreInterface $store,
		array $options = [],
		protected bool $autoCommit = true
	) {
		$this->options = array_replace_recursive($this->options, $options);

		$this->gc();

		$this->start();
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		if ($this->autoCommit) {
			$this->commit();
		}
	}

	/**
	 * Starts the session.
	 */
	protected function start(): void
	{
		// Get the session id from the cookie or generate a new one if it doesn't exist.

		$this->sessionId = $this->request->cookies->getSigned($this->options['name'], false) ?: $this->generateId();

		// Create a new / update the existing session cookie

		$this->setCookie();

		// Load the session data

		$this->loadData();

		// Create a session token if we don't have one

		if (empty($this->sessionData['mako:token'])) {
			$this->sessionData['mako:token'] = $this->generateId();
		}

		$this->token = $this->sessionData['mako:token'];
	}

	/**
	 * Disables auto commit.
	 */
	public function disableAutoCommit(): void
	{
		$this->autoCommit = false;
	}

	/**
	 * Enables auto commit.
	 */
	public function enableAutoCommit(): void
	{
		$this->autoCommit = true;
	}

	/**
	 * Writes data to session store.
	 */
	public function commit(): void
	{
		// Replace old flash data with new

		$this->sessionData['mako:flashdata'] = $this->flashData;

		// Write session data

		if (!$this->destroyed) {
			$this->store->write($this->sessionId, $this->sessionData, $this->options['data_ttl']);
		}
	}

	/**
	 * Calls the session store garbage collector.
	 */
	public function gc(bool $force = false): void
	{
		if ($force || mt_rand(1, 100) === 100) {
			$this->store->gc($this->options['data_ttl']);
		}
	}

	/**
	 * Generates a session id.
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
		if ($this->options['cookie_options']['secure'] && !$this->request->isSecure()) {
			throw new SessionException('Attempted to set a secure cookie over a non-secure connection.');
		}

		$this->response->cookies->addSigned($this->options['name'], $this->sessionId, $this->options['cookie_ttl'], $this->options['cookie_options']);
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
	 */
	public function getId(): string
	{
		return $this->sessionId;
	}

	/**
	 * Regenerate the session id and returns it.
	 */
	public function regenerateId(bool $keepOld = false): string
	{
		// Delete old data if we don't want to keep it

		if (!$keepOld) {
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
	 */
	public function getData(): array
	{
		return $this->sessionData;
	}

	/**
	 * Store a value in the session.
	 */
	public function put(string $key, mixed $value): void
	{
		$this->sessionData[$key] = $value;
	}

	/**
	 * Returns TRUE if key exists in the session and FALSE if not.
	 */
	public function has(string $key): bool
	{
		return isset($this->sessionData[$key]);
	}

	/**
	 * Returns a value from the session.
	 */
	public function get(string $key, mixed $default = null): mixed
	{
		return $this->sessionData[$key] ?? $default;
	}

	/**
	 * Gets a value from the session and replaces it.
	 */
	public function getAndPut(string $key, mixed $value, mixed $default = null): mixed
	{
		$storedValue = $this->get($key, $default);

		$this->put($key, $value);

		return $storedValue;
	}

	/**
	 * Gets a value from the session and removes it.
	 */
	public function getAndRemove(string $key, mixed $default = null): mixed
	{
		$storedValue = $this->get($key, $default);

		$this->remove($key);

		return $storedValue;
	}

	/**
	 * Removes a value from the session.
	 */
	public function remove(string $key): void
	{
		unset($this->sessionData[$key]);
	}

	/**
	 * Store a flash value in the session.
	 */
	public function putFlash(string $key, mixed $value): void
	{
		$this->flashData[$key] = $value;
	}

	/**
	 * Returns TRUE if key exists in the session and FALSE if not.
	 */
	public function hasFlash(string $key): bool
	{
		return isset($this->sessionData['mako:flashdata'][$key]);
	}

	/**
	 * Returns a flash value from the session.
	 */
	public function getFlash(string $key, mixed $default = null): mixed
	{
		return $this->sessionData['mako:flashdata'][$key] ?? $default;
	}

	/**
	 * Removes a value from the session.
	 */
	public function removeFlash(string $key): void
	{
		unset($this->sessionData['mako:flashdata'][$key]);
	}

	/**
	 * Extends the lifetime of the flash data by one request.
	 */
	public function reflash(array $keys = []): void
	{
		$flashData = $this->sessionData['mako:flashdata'] ?? [];

		$flashData = empty($keys) ? $flashData : array_intersect_key($flashData, array_flip($keys));

		$this->flashData = [...$this->flashData, ...$flashData];
	}

	/**
	 * Returns the session token.
	 */
	public function getToken(): string
	{
		return $this->token;
	}

	/**
	 * Generates a new session token and returns it.
	 */
	public function regenerateToken(): string
	{
		return $this->token = $this->sessionData['mako:token'] = $this->generateId();
	}

	/**
	 * Validates the provided token.
	 */
	public function validateToken(#[SensitiveParameter] string $token): bool
	{
		return hash_equals($this->token, $token);
	}

	/**
	 * Returns random security token.
	 */
	public function generateOneTimeToken(): string
	{
		if (!empty($this->sessionData['mako:tokens'])) {
			$this->sessionData['mako:tokens'] = array_slice($this->sessionData['mako:tokens'], 0, (static::MAX_TOKENS - 1));
		}
		else {
			$this->sessionData['mako:tokens'] = [];
		}

		$token = $this->generateId();

		array_unshift($this->sessionData['mako:tokens'], $token);

		return $token;
	}

	/**
	 * Validates security token.
	 */
	public function validateOneTimeToken(#[SensitiveParameter] string $token): bool
	{
		if (!empty($this->sessionData['mako:tokens'])) {
			foreach ($this->sessionData['mako:tokens'] as $key => $value) {
				if (hash_equals($value, $token)) {
					unset($this->sessionData['mako:tokens'][$key]);

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

		$this->response->cookies->delete($this->options['name'], $this->options['cookie_options']);

		$this->destroyed = true;
	}
}
