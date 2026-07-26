<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\app;

use mako\cli\input\arguments\NamedArgument;
use mako\file\FileSystem;
use mako\reactor\attributes\CommandArguments;
use mako\reactor\attributes\CommandDescription;
use mako\reactor\Command;
use mako\security\Key;

use function intdiv;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function rtrim;
use function sprintf;

/**
 * Command that generates a cryptographic key.
 */
#[CommandDescription('Generates a cryptographic key.')]
#[CommandArguments(
	new NamedArgument('key-size', 's', 'Size of the key to generate in bits (default = 256)', NamedArgument::IS_OPTIONAL | NamedArgument::IS_INT, default: 256),
	new NamedArgument('dotenv', 'd', 'Path to .env file', NamedArgument::IS_OPTIONAL),
	new NamedArgument('dotenv-key', 'k', 'Name of the .env key to generate', NamedArgument::IS_OPTIONAL),
)]
class GenerateKey extends Command
{
	/**
	 * Updates or adds a key in a .env file.
	 */
	protected function updateDotEnv(FileSystem $fileSystem, string $path, string $envKey, string $key): void
	{
		$contents = $fileSystem->get($path);

		$pattern = '/^(' . preg_quote($envKey, '/') . '=).*$/m';

		if (preg_match($pattern, $contents)) {
			$contents = preg_replace($pattern, '${1}' . "'{$key}'", $contents);
		}
		else {
			$contents = rtrim($contents, "\r\n") . PHP_EOL;
			$contents .= "{$envKey}='{$key}'" . PHP_EOL;
		}

		$fileSystem->put($path, $contents);
	}

	/**
	 * Executes the command.
	 */
	public function execute(FileSystem $fileSystem, int $keySize, ?string $dotenv, ?string $dotenvKey): int
	{
		if ($keySize % 8 !== 0) {
			$this->error('Key size must be a multiple of 8 bits.');

			return static::STATUS_ERROR;
		}

		$key = Key::generateEncoded(intdiv($keySize, 8));

		if ($dotenv !== null && $dotenvKey !== null) {
			if (!$fileSystem->has($dotenv)) {
				$this->error('The .env file does not exist.');

				return static::STATUS_ERROR;
			}

			if (!$fileSystem->isReadable($dotenv)) {
				$this->error('The .env file is not readable.');

				return static::STATUS_ERROR;
			}

			if (!$fileSystem->isWritable($dotenv)) {
				$this->error('The .env file is not writable.');

				return static::STATUS_ERROR;
			}

			// Update key in file

			$this->updateDotEnv($fileSystem, $dotenv, $dotenvKey, $key);

			$this->write(sprintf('Generated value for "<yellow>%s</yellow>" in "<yellow>%s</yellow>".', $dotenvKey, $dotenv));

			return static::STATUS_SUCCESS;
		}

		$this->nl();
		$this->write('Your encryption key: "<yellow>{$key}</yellow>".');
		$this->nl();

		return static::STATUS_SUCCESS;
	}
}
