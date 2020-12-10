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
	 *
	 * @param  \mako\i18n\I18n                          $i18n I18n
	 * @return \mako\validator\rules\I18nAwareInterface
	 */
	public function setI18n(I18n $i18n): I18nAwareInterface;

	/**
	 * Returns the translated error message.
	 *
	 * @param  string      $field   Field name
	 * @param  string      $rule    Rule name
	 * @param  string|null $package Package name
	 * @return string
	 */
	public function getTranslatedErrorMessage(string $field, string $rule, ?string $package = null): string;
}
