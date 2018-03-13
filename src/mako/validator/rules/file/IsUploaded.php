<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\file;

use mako\http\request\UploadedFile;
use mako\validator\rules\Rule;
use mako\validator\rules\RuleInterface;

/**
 * Is uploaded rule.
 *
 * @author Frederic G. Østby
 */
class IsUploaded extends Rule implements RuleInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return $value instanceof UploadedFile && $value->isUploaded();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s must be an uploaded file.', $field);
	}
}
