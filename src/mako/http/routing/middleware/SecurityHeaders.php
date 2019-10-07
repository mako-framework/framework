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

use function base64_encode;
use function implode;
use function json_encode;
use function random_bytes;

/**
 * Security headers middleware.
 *
 * @author Frederic G. Østby
 */
class SecurityHeaders implements MiddlewareInterface
{
	/**
	 * Disable content security policy header?
	 *
	 * @var bool
	 */
	protected $disableCsp;

	/**
	 * Container.
	 *
	 * @var \mako\syringe\Container;
	 */
	protected $container;

	/**
	 * Report to.
	 *
	 * @var array|null
	 */
	protected $reportTo;

	/**
	 * Security headers.
	 *
	 * @var array|null
	 */
	protected $headers =
	[
		'X-Content-Type-Options' => 'nosniff',
		'X-Frame-Options'        => 'sameorigin',
		'X-XSS-Protection'       => '1; mode=block',
	];

	/**
	 * Should we only report content security policy violations?
	 *
	 * @var bool
	 */
	protected $cspReportOnly = false;

	/**
	 * Content security policy directives.
	 *
	 * @var array|null
	 */
	protected $cspDirectives =
	[
		'base-uri'    => ['self'],
		'default-src' => ['self'],
		'object-src'  => ['none'],
	];

	/**
	 * Content security policy nonce.
	 *
	 * @var string|null
	 */
	protected $cspNonce;

	/**
	 * Content security policy nonce view variable name.
	 *
	 * @var string
	 */
	protected $cspNonceVariableName = '_csp_nonce_';

	/**
	 * Constructor.
	 *
	 * @param bool                    $disableCsp Should the content security policy header be disabled?
	 * @param \mako\syringe\Container $container  Container
	 */
	public function __construct(bool $disableCsp = false, Container $container)
	{
		$this->disableCsp = $disableCsp;

		$this->container = $container;
	}

	/**
	 * Builds the "Report-To" header value.
	 *
	 * @return string
	 */
	protected function buildReportToValue(): string
	{
		$endpoints = [];

		foreach($this->reportTo as $endpoint)
		{
			$endpoints[] = json_encode($endpoint);
		}

		return implode(', ', $endpoints);
	}

	/**
	 * Generates a random content security policy nonce.
	 *
	 * @return string
	 */
	protected function generateCspNonce(): string
	{
		return base64_encode(random_bytes(16));
	}

	/**
	 * Returns the content security policy nonce.
	 *
	 * @return string
	 */
	protected function getCspNonce(): string
	{
		if($this->cspNonce === null)
		{
			$this->cspNonce = $this->generateCspNonce();
		}

		return $this->cspNonce;
	}

	/**
	 * Builds the "Content-Security-Policy" header value.
	 *
	 * @return string
	 */
	protected function buildCspValue(): string
	{
		$directives = [];

		foreach($this->cspDirectives as $name => $directive)
		{
			if($directive === true)
			{
				$directives[] = $name;

				continue;
			}

			$directiveString = $name;

			foreach($directive as $value)
			{
				switch($value)
				{
					case 'self':
					case 'unsafe-inline':
					case 'unsafe-eval':
					case 'none':
						$value = "'{$value}'";
						break;
					case 'nonce':
						$value = "'nonce-{$this->getCspNonce()}'";
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
	protected function assignCspNonceViewVariable(): void
	{
		if($this->container->has(ViewFactory::class))
		{
			$this->container->get(ViewFactory::class)->assign($this->cspNonceVariableName, $this->getCspNonce());
		}
	}

	/**
	 * Should the CSP header be added to response?
	 *
	 * @param  \mako\http\Response $response Response
	 * @return bool
	 */
	protected function shouldAddCspHeader(Response $response): bool
	{
		return $response->getType() === 'text/html';
	}

	/**
	 * {@inheritdoc}
	 */
	public function execute(Request $request, Response $response, Closure $next): Response
	{
		$headers = $response->getHeaders();

		if($this->reportTo !== null)
		{
			$headers->add('Report-To', $this->buildReportToValue());
		}

		if($this->headers !== null)
		{
			foreach($this->headers as $name => $value)
			{
				$headers->add($name, $value);
			}
		}

		if($this->disableCsp === false && $this->cspDirectives !== null)
		{
			$cspHeader = $this->buildCspValue();

			if($this->cspNonce !== null)
			{
				$this->assignCspNonceViewVariable();
			}
		}

		$response = $next($request, $response);

		if($this->disableCsp === false && $this->cspDirectives !== null && $this->shouldAddCspHeader($response))
		{
			$headers->add($this->cspReportOnly ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy', $cspHeader);
		}

		return $response;
	}
}
