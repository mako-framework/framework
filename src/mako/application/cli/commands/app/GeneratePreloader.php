<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\app;

use mako\application\Application;
use mako\cli\input\arguments\Argument;
use mako\cli\input\Input;
use mako\cli\output\Output;
use mako\file\FileSystem;
use mako\preload\PreloaderGenerator;
use mako\reactor\Command;

use function array_merge;
use function array_unique;
use function count;
use function sprintf;

/**
 * Command that generates an opcache preloader script.
 */
class GeneratePreloader extends Command
{
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Generates a opcache preloader script.';

	/**
	 * @var \mako\application\Application
	 */
	protected $app;

	/**
	 * @var \mako\file\FileSystem
	 */
	protected $fileSystem;

	/**
	 * Constructor.
	 *
	 * @param \mako\cli\input\Input         $input      Input
	 * @param \mako\cli\output\Output       $output     Output
	 * @param \mako\application\Application $app        Application
	 * @param \mako\file\FileSystem         $fileSystem File system
	 */
	public function __construct(Input $input, Output $output, Application $app, FileSystem $fileSystem)
	{
		parent::__construct($input, $output);

		$this->app = $app;

		$this->fileSystem = $fileSystem;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getArguments(): array
	{
		return
		[
			new Argument('-i|--ignore-core-classes', 'Should the default selection of core classes be ignored?', Argument::IS_BOOL),
		];
	}

	/**
	 * Gets returns an array of common framework classes.
	 *
	 * @return array
	 */
	protected function getCoreClasses(): array
	{
		return $this->fileSystem->include(__DIR__ . '/preloader/core.php');
	}

	/**
	 * Returns an array of all the classes that should be preloaded.
	 *
	 * @param  bool  $ignoreCoreClasses Should the default selection of core classes be ignored?
	 * @return array
	 */
	protected function getClasses(bool $ignoreCoreClasses): array
	{
		$classes = [];

		if(!$ignoreCoreClasses)
		{
			$classes = $this->getCoreClasses();
		}

		if($this->fileSystem->has("{$this->app->getPath()}/config/preload.php"))
		{
			$classes = array_merge($classes, $this->app->getConfig()->get('preload'));
		}

		return array_unique($classes);
	}

	/**
	 * Returns the path to where the opcache preloder script should be stored.
	 *
	 * @return string
	 */
	protected function getStoragePath(): string
	{
		return $this->app->getStoragePath();
	}

	/**
	 * Generates an opcache preloader script.
	 *
	 * @param  bool $ignoreCoreClasses Should the default selection of core classes be ignored?
	 * @return int
	 */
	public function execute(bool $ignoreCoreClasses = false): int
	{
		$path = "{$this->getStoragePath()}/preload.php";

		$classes = $this->getClasses($ignoreCoreClasses);

		if(count($classes) === 0)
		{
			$this->error('You must preload at least one class.');

			return static::STATUS_ERROR;
		}

		$this->fileSystem->put($path, (new PreloaderGenerator)->generatePreloader($classes));

		$this->write(sprintf('Preload file written to <yellow>%s</yellow>.', $path));

		return static::STATUS_SUCCESS;
	}
}
