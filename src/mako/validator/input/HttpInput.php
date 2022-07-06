<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\input;

use mako\http\Request;
use mako\http\routing\URLBuilder;

/**
 * HTTP input.
 */
abstract class HttpInput extends Input implements HttpInputInterface
{
	/**
	 * Should we redirect the client if possible?
	 *
	 * @var bool
	 */
	protected $shouldRedirect = true;

	/**
	 * Should the old input be included?
	 *
	 * @var bool
	 */
	protected $shouldIncludeOldInput = true;

	/**
	 * Constructor.
	 *
	 * @param \mako\http\Request            $request    Request
	 * @param \mako\http\routing\URLBuilder $urlBuilder URL builder
	 */
	public function __construct(
		protected Request $request,
		protected URLBuilder $urlBuilder
	)
	{}

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
