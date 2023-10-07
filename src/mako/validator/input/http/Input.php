<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\input\http;

use mako\http\Request;
use mako\http\routing\URLBuilder;
use mako\validator\input\Input as BaseInput;

/**
 * HTTP input.
 */
abstract class Input extends BaseInput implements InputInterface
{
	/**
	 * Should we redirect the client if possible?
	 */
	protected bool $shouldRedirect = true;

	/**
	 * Should the old input be included?
	 */
	protected bool $shouldIncludeOldInput = true;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Request $request,
		protected URLBuilder $urlBuilder
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function getInput(): array
	{
		return $this->request->getData()->all();
	}

	/**
	 * {@inheritDoc}
	 */
	public function shouldRedirect(): bool
	{
		return $this->shouldRedirect;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRedirectUrl(): string
	{
		return $this->urlBuilder->current();
	}

	/**
	 * {@inheritDoc}
	 */
	public function shouldIncludeOldInput(): bool
	{
		return $this->shouldIncludeOldInput;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOldInput(): array
	{
		return $this->request->getData()->all();
	}
}
