<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\app;

use mako\application\Application;
use mako\classes\preload\PreloaderGenerator;
use mako\cli\input\arguments\Argument;
use mako\cli\input\arguments\NamedArgument;
use mako\cli\input\Input;
use mako\cli\output\Output;
use mako\file\FileSystem;
use mako\reactor\attributes\CommandArguments;
use mako\reactor\attributes\CommandDescription;
use mako\reactor\Command;

use function array_unique;
use function count;
use function sprintf;

/**
 * Command that generates an opcache preloader script.
 */
#[CommandDescription('Generates a opcache preloader script.')]
#[CommandArguments(
	new NamedArgument('ignore-core-classes', 'i', 'Should the default selection of core classes be ignored?', Argument::IS_BOOL),
	new NamedArgument('output-path', 'o', 'Path to where the preloder script should be written', Argument::IS_OPTIONAL),
)]
class GeneratePreloader extends Command
{
	/**
	 * Constructor.
	 */
	public function __construct(
		Input $input,
		Output $output,
		protected Application $app,
		protected FileSystem $fileSystem
	) {
		parent::__construct($input, $output);
	}

	/**
	 * Gets returns an array of common framework classes.
	 */
	protected function getCoreClasses(): array
	{
		return $this->fileSystem->include(__DIR__ . '/preloader/core.php');
	}

	/**
	 * Returns an array of all the classes that should be preloaded.
	 */
	protected function getClasses(bool $ignoreCoreClasses): array
	{
		$classes = [];

		if (!$ignoreCoreClasses) {
			$classes = $this->getCoreClasses();
		}

		if ($this->fileSystem->has("{$this->app->getPath()}/config/preload.php")) {
			$classes = [...$classes, ...$this->app->getConfig()->get('preload')];
		}

		return array_unique($classes);
	}

	/**
	 * Returns the path to where the opcache preloder script should be stored.
	 */
	protected function getOutputPath(?string $outputPath): string
	{
		return $outputPath ?? "{$this->app->getStoragePath()}/preload.php";
	}

	/**
	 * Generates an opcache preloader script.
	 */
	public function execute(bool $ignoreCoreClasses = false, ?string $outputPath = null): int
	{
		$path = $this->getOutputPath($outputPath);

		$classes = $this->getClasses($ignoreCoreClasses);

		if (count($classes) === 0) {
			$this->error('You must preload at least one class.');

			return static::STATUS_ERROR;
		}

		$this->fileSystem->put($path, (new PreloaderGenerator)->generatePreloader($classes));

		$this->write(sprintf('Preload file written to "<yellow>%s</yellow>".', $path));

		return static::STATUS_SUCCESS;
	}
}
