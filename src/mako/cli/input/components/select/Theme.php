<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\components\select;

use function sprintf;

/**
 * Base theme.
 */
class Theme
{
	/**
	 * Active pointer.
	 */
	protected const string ACTIVE_POINTER = '>';

	/**
	 * Inactive pointer.
	 */
	protected const string INACTIVE_POINTER = ' ';

	/**
	 * Selected.
	 */
	protected const string SELECTED = '●';

	/**
	 * Unselected.
	 */
	protected const string UNSELECTED = '○';

	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $activePointerTemplate = '%s',
		protected string $inactivePointerTemplate = '%s',
		protected string $selectedTemplate = '%s',
		protected string $unselectedTemplate = '%s'
	) {
	}

	/**
	 * Returns the active pointer.
	 */
	public function getActivePointer(): string
	{
		return sprintf($this->activePointerTemplate, static::ACTIVE_POINTER);
	}

	/**
	 * Returns the inactive pointer.
	 */
	public function getInactivePointer(): string
	{
		return sprintf($this->inactivePointerTemplate, static::INACTIVE_POINTER);
	}

	/**
	 * Returns the selected.
	 */
	public function getSelected(): string
	{
		return sprintf($this->selectedTemplate, static::SELECTED);
	}

	/**
	 * Returns the unselected.
	 */
	public function getUnselected(): string
	{
		return sprintf($this->unselectedTemplate, static::UNSELECTED);
	}
}
