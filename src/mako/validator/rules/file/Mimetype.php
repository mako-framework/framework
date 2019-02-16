<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\file;

use mako\validator\rules\Rule;
use mako\validator\rules\RuleInterface;

use function implode;
use function in_array;
use function sprintf;

/**
 * Mimetype rule.
 *
 * @author Frederic G. Østby
 */
class Mimetype extends Rule implements RuleInterface
{
	/**
	 * Mimetypes.
	 *
	 * @var array
	 */
	protected $mimetypes;

	/**
	 * Constructor.
	 *
	 * @param string|array $mimetype Mimetype or array of mimetypes
	 */
	public function __construct($mimetype)
	{
		$this->mimetypes = (array) $mimetype;
	}

	/**
	 * I18n parameters.
	 *
	 * @var array
	 */
	protected $i18nParameters = ['mimetypes'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return in_array($value->getMimeType(), $this->mimetypes);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s must be a file of type: %2$s.', $field, implode(', ', $this->mimetypes));
	}
}
