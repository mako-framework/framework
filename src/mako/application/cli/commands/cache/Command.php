<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\cache;

use mako\cli\input\Input;
use mako\cli\output\Output;
use mako\common\traits\SuggestionTrait;
use mako\config\Config;
use mako\reactor\Command as BaseCommand;

use function array_keys;
use function in_array;

/**
 * Cache base command.
 */
abstract class Command extends BaseCommand
{
	use SuggestionTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		Input $input,
		Output $output,
		protected Config $config
	) {
		parent::__construct($input, $output);
	}

	/**
	 * Checks if the configuration exists.
	 */
	protected function checkConfigurationExistence(string $configuration): bool
	{
		$configurations = array_keys($this->config->get('cache.configurations'));

		if (!in_array($configuration, $configurations)) {
			$message = "The \"<bold>{$configuration}</bold>\" configuration does not exist.";

			if (($suggestion = $this->suggest($configuration, $configurations)) !== null) {
				$message .= " Did you mean \"<bold>{$suggestion}</bold>\"?";
			}

			$this->error($message);

			return false;
		}

		return true;
	}
}
