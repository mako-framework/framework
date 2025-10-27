<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\components\confirmation;

use function sprintf;

/**
 * Base theme.
 */
class Theme
{
	/**
	 * Selected.
	 */
	protected const string SELECTED = '●';

	/**
	 * Unselected.
	 */
	protected const string UNSELECTED = '○';

	/**
	 * Input prefix.
	 */
	protected const string INPUT_PREFIX = '>';

	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $trueTemplate = '%s',
		protected string $falseTemplate = '%s',
		protected string $inputPrefixTemplate = '%s'
	) {
	}

	/**
	 * Returns the input prefix.
	 */
	public function getInputPrefix(): string
	{
		return sprintf($this->inputPrefixTemplate, static::INPUT_PREFIX);
	}

	/**
	 * Returns the selected.
	 */
	public function getSelected(bool $bool): string
	{
		return sprintf($bool ? $this->trueTemplate : $this->falseTemplate, static::SELECTED);
	}

	/**
	 * Returns the unselected.
	 */
	public function getUnselected(bool $bool): string
	{
		return sprintf($bool ? $this->trueTemplate : $this->falseTemplate, static::UNSELECTED);
	}
}
