<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use mako\validator\rules\traits\DoesntValidateWhenEmptyTrait;
use mako\validator\rules\traits\I18nAwareTrait;

/**
 * Base rule.
 */
abstract class Rule implements I18nAwareInterface
{
	use I18nAwareTrait;
	use DoesntValidateWhenEmptyTrait;
}
