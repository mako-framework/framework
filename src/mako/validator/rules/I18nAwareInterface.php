<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use mako\i18n\I18n;

/**
 * I18n aware interface.
 */
interface I18nAwareInterface
{
	/**
	 * Sets the I18n instance.
	 */
	public function setI18n(I18n $i18n): I18nAwareInterface;

	/**
	 * Returns the translated error message.
	 */
	public function getTranslatedErrorMessage(string $field, string $rule, ?string $package = null): string;
}
