<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\input\http;

use mako\http\Request;
use mako\http\routing\URLBuilder;
use mako\validator\input\Input as BaseInput;
use Override;

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
	#[Override]
	public function getInput(): array
	{
		return $this->request->getData()->all();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function shouldRedirect(): bool
	{
		return $this->shouldRedirect;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getRedirectUrl(): string
	{
		return $this->urlBuilder->current();
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function shouldIncludeOldInput(): bool
	{
		return $this->shouldIncludeOldInput;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getOldInput(): array
	{
		return $this->request->getData()->all();
	}
}
