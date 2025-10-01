<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\config;

use mako\config\loaders\LoaderInterface;
use mako\utility\Arr;

use function explode;
use function str_contains;

/**
 * Config class.
 */
class Config
{
	/**
	 * Configuration.
	 */
	protected array $configuration = [];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected LoaderInterface $loader,
		protected ?string $environment = null
	) {
	}

	/**
	 * Returns the config loader.
	 */
	public function getLoader(): LoaderInterface
	{
		return $this->loader;
	}

	/**
	 * Returns the currently loaded configuration.
	 */
	public function getLoadedConfiguration(): array
	{
		return $this->configuration;
	}

	/**
	 * Sets the environment.
	 */
	public function setEnvironment(string $environment): void
	{
		$this->environment = $environment;
	}

	/**
	 * Parses the config key.
	 */
	protected function parseKey(string $key): array
	{
		return str_contains($key, '.') ? explode('.', $key, 2) : [$key, null];
	}

	/**
	 * Loads the configuration file.
	 */
	protected function load(string $file): void
	{
		$this->configuration[$file] = $this->loader->load($file, $this->environment);
	}

	/**
	 * Returns config value or entire config array from a file.
	 */
	public function get(string $key, mixed $default = null): mixed
	{
		[$file, $path] = $this->parseKey($key);

		if (!isset($this->configuration[$file])) {
			$this->load($file);
		}

		return $path === null ? $this->configuration[$file] : Arr::get($this->configuration[$file], $path, $default);
	}

	/**
	 * Sets a config value.
	 */
	public function set(string $key, mixed $value): void
	{
		[$file] = $this->parseKey($key);

		if (!isset($this->configuration[$file])) {
			$this->load($file);
		}

		Arr::set($this->configuration, $key, $value);
	}

	/**
	 * Removes a value from the configuration.
	 */
	public function remove(string $key): bool
	{
		return Arr::delete($this->configuration, $key);
	}
}
