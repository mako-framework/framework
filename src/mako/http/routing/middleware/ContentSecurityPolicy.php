<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing\middleware;

use Closure;
use mako\http\Request;
use mako\http\Response;
use mako\syringe\Container;
use mako\view\ViewFactory;
use Override;

use function base64_encode;
use function implode;
use function json_encode;
use function random_bytes;

/**
 * Content security policy middleware.
 */
class ContentSecurityPolicy implements MiddlewareInterface
{
	/**
	 * Report to.
	 */
	protected array $reportTo = [];

	/**
	 * Should we only report content security policy violations?
	 */
	protected bool $reportOnly = false;

	/**
	 * Content security policy directives.
	 */
	protected array $directives = [
		'base-uri'    => ['self'],
		'default-src' => ['self'],
		'object-src'  => ['none'],
	];

	/**
	 * Content security policy nonce.
	 */
	protected string $nonce;

	/**
	 * Content security policy nonce view variable name.
	 */
	protected string $nonceVariableName = '_csp_nonce_';

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Container $container
	) {
	}

	/**
	 * Builds the "Report-To" header value.
	 */
	protected function buildReportToValue(): string
	{
		$endpoints = [];

		foreach ($this->reportTo as $endpoint) {
			$endpoints[] = json_encode($endpoint);
		}

		return implode(', ', $endpoints);
	}

	/**
	 * Generates a random content security policy nonce.
	 */
	protected function generateNonce(): string
	{
		return base64_encode(random_bytes(16));
	}

	/**
	 * Returns the content security policy nonce.
	 */
	protected function getNonce(): string
	{
		if (empty($this->nonce)) {
			$this->nonce = $this->generateNonce();
		}

		return $this->nonce;
	}

	/**
	 * Builds the "Content-Security-Policy" header value.
	 */
	protected function buildValue(): string
	{
		$directives = [];

		foreach ($this->directives as $name => $directive) {
			if ($directive === true) {
				$directives[] = $name;

				continue;
			}

			$directiveString = $name;

			foreach ($directive as $value) {
				switch ($value) {
					case 'self':
					case 'unsafe-inline':
					case 'unsafe-eval':
					case 'none':
						$value = "'{$value}'";
						break;
					case 'nonce':
						$value = "'nonce-{$this->getNonce()}'";
						break;
				}

				$directiveString .= " {$value}";
			}

			$directives[] = $directiveString;
		}

		return implode('; ', $directives);
	}

	/**
	 * Assigns a global view variable containing the content security policy nonce.
	 */
	protected function assignNonceViewVariable(): void
	{
		if ($this->container->has(ViewFactory::class)) {
			$this->container->get(ViewFactory::class)->assign($this->nonceVariableName, $this->getNonce());
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function execute(Request $request, Response $response, Closure $next): Response
	{
		if (!empty($this->reportTo)) {
			$response->headers->add('Report-To', $this->buildReportToValue());
		}

		$response->headers->add($this->reportOnly ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy', $this->buildValue());

		if (!empty($this->nonce)) {
			$this->assignNonceViewVariable();
		}

		return $next($request, $response);
	}
}
