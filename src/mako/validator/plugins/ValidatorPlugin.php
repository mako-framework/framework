<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\validator\plugins;

use mako\validator\plugins\ValidatorPluginInterface;

/**
 * Base plugin.
 *
 * @author  Frederic G. Østby
 */

abstract class ValidatorPlugin implements ValidatorPluginInterface
{
	/**
	 * Rule name.
	 *
	 * @var string
	 */

	protected $ruleName = '';

	/**
	 * Package name.
	 *
	 * @var string
	 */

	protected $packageName = '';

	/**
	 * {@inheritdoc}
	 */

	public function getRuleName()
	{
		return $this->ruleName;
	}

	/**
	 * {@inheritdoc}
	 */

	public function getPackageName()
	{
		return $this->packageName;
	}
}