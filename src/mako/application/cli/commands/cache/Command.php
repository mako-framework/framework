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
	 * Configuration.
	 *
	 * @var \mako\config\Config
	 */
	protected $config;

	/**
	 * Constructor.
	 *
	 * @param \mako\cli\input\Input   $input  Input
	 * @param \mako\cli\output\Output $output Output
	 * @param \mako\config\Config     $config Config
	 */
	public function __construct(Input $input, Output $output, Config $config)
	{
		parent::__construct($input, $output);

		$this->config = $config;
	}

	/**
	 * Checks if the configuration exists.
	 *
	 * @param  string $configuration Configuration name
	 * @return bool
	 */
	protected function checkConfigurationExistence(string $configuration): bool
	{
		$configurations = array_keys($this->config->get('cache.configurations'));

		if(!in_array($configuration, $configurations))
		{
			$message = "The [ {$configuration} ] configuration does not exist.";

			if(($suggestion = $this->suggest($configuration, $configurations)) !== null)
			{
				$message .= " Did you mean [ {$suggestion} ]?";
			}

			$this->error($message);

			return false;
		}

		return true;
	}
}
