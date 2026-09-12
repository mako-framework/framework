<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\package;

use Composer\InstalledVersions;
use mako\application\Application;
use mako\cli\input\arguments\Argument;
use mako\cli\input\arguments\NamedArgument;
use mako\cli\input\arguments\PositionalArgument;
use mako\cli\input\Input;
use mako\cli\output\Output;
use mako\file\FileSystem;
use mako\file\Permission;
use mako\file\Permissions;
use mako\reactor\attributes\CommandArguments;
use mako\reactor\attributes\CommandDescription;
use mako\reactor\Command;

use function array_diff;
use function array_unique;
use function basename;
use function file_get_contents;
use function in_array;
use function is_file;
use function json_decode;
use function realpath;
use function sprintf;
use function str_replace;

/**
 * Command that installs package resources into the application.
 */
#[CommandDescription('Installs package resources into the application.')]
#[CommandArguments(
	new PositionalArgument('package', 'Package names to install', Argument::IS_ARRAY | Argument::IS_OPTIONAL),
	new NamedArgument('force', 'f', 'Override existing files without confirming', Argument::IS_BOOL),
)]
class Install extends Command
{
	/**
	 * Whitelisted packages.
	 */
	protected const array WHITELIST = [
		'mako/mcp',
		'mako/repl',
		'mako/toolbar',
	];

	/**
	 * Should we force overriding of existing files?
	 */
	protected bool $force = false;

	/**
	 * {@inheritDoc}
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
	 * Returns the file namespace of the package.
	 */
	protected function getFileNamespace(string $packageName): string
	{
		return str_replace('/', '-', $packageName);
	}

	/**
	 * Creates the directory if it doesn't already exist.
	 */
	protected function createDirectoryIfItDoesNotExist(string $path): void
	{
		if (!$this->fileSystem->has($path)) {
			$this->fileSystem->createDirectory(
				$path,
				new Permissions(
					Permission::OwnerFull,
					Permission::GroupExecuteRead,
					Permission::PublicExecuteRead
				),
				recursive: true
			);
		}
	}

	/**
	 * Copies the file unless it already exists and overriding isn't forced.
	 */
	protected function copyFile(string $source, string $configPath): bool
	{
		$destination = $configPath . '/' . basename($source);

		if (!$this->force && $this->fileSystem->has($destination)) {
			return false;
		}

		$this->fileSystem->copy($source, $destination);

		$this->fileSystem->setPermissions(
			$destination,
			new Permissions(
				Permission::OwnerWriteRead,
				Permission::GroupRead,
				Permission::PublicRead
			)
		);

		return true;
	}

	/**
	 * Copies config file(s).
	 */
	protected function copyConfig(string $packageName, string $packagePath): bool
	{
		// Return early if the package doesn't have any config files

		if (
			!$this->fileSystem->has("{$packagePath}/config")
			|| empty($files = $this->fileSystem->glob("{$packagePath}/config/*.php"))
		) {
			return false;
		}

		// Build config path

		$configPath = $this->app->getPath() . '/config/packages/' . $this->getFileNamespace($packageName);

		// Create directory if it doesn't already exist

		$this->createDirectoryIfItDoesNotExist($configPath);

		// Copy config file(s)

		$copied = false;

		foreach ($files as $file) {
			$copied = $this->copyFile($file, $configPath) || $copied;
		}

		return $copied;
	}

	/**
	 * Install resources.
	 */
	public function execute(array $package = [], bool $force = false): int
	{
		$this->force = $force;

		$found = [];
		$installed = 0;
		$success = true;

		$packagesToInstall = $package === [] ? static::WHITELIST : array_unique($package);

		$this->nl();

		// Loop over packages and install resources

		foreach (InstalledVersions::getInstalledPackages() as $packageName) {
			if (!in_array($packageName, $packagesToInstall)) {
				continue;
			}

			$packagePath = InstalledVersions::getInstallPath($packageName);

			if (empty($packagePath)) {
				continue;
			}

			$packagePath = realpath($packagePath);

			$composerJson = "{$packagePath}/composer.json";

			if (!is_file($composerJson)) {
				continue;
			}

			$found[] = $packageName;

			$composerData = json_decode(
				file_get_contents($composerJson),
				associative: true
			);

			$makoInstall = $composerData['extra']['mako']['install'] ?? null;

			if ($makoInstall === null) {
				if ($package !== []) {
					$this->write(sprintf('<blue>*</blue> The "<yellow>%s</yellow>" package has no installable resources.', $packageName));
					$this->nl();
				}

				continue;
			}

			$installedResources = false;

			if (($makoInstall['config'] ?? false) && $this->copyConfig($packageName, $packagePath)) {
				$this->write(sprintf('<green>*</green> Installed config file(s) from "<yellow>%s</yellow>".', $packageName));
				$this->nl();
				$installedResources = true;
			}

			$installedResources && $installed++;
		}

		// Print report

		if ($package !== []) {
			$notFound = array_diff($packagesToInstall, $found);

			if ($notFound !== []) {
				$success = false;

				$this->error('The following packages were not found:');

				foreach ($notFound as $notFoundPackage) {
					$this->error(" - {$notFoundPackage}");
				}

				$this->nl();
			}
		}

		$this->write(sprintf('Installed resources from <yellow>%s</yellow> %s.', $installed, $installed === 1 ? 'package' : 'packages'));

		$this->nl();

		return $success ? Command::STATUS_SUCCESS : Command::STATUS_ERROR;
	}
}
