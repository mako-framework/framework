<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\cli;

use ErrorException;
use mako\cli\output\helpers\Alert;
use mako\cli\output\Output;
use mako\error\handlers\HandlerInterface;
use Throwable;

use function array_keys;
use function in_array;

/**
 * Development handler.
 */
class DevelopmentHandler implements HandlerInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Output $output
	) {
	}

	/**
	 * Escape formatting tags.
	 */
	protected function escape(string $string): string
	{
		if (($formatter = $this->output->getFormatter()) === null) {
			return $string;
		}

		return $formatter->escape($string);
	}

	/**
	 * Determines the exception type.
	 */
	protected function determineExceptionType(Throwable $exception): string
	{
		if ($exception instanceof ErrorException) {
			$code = $exception->getCode();

			$codes = [
				E_ERROR             => 'Fatal Error',
				E_PARSE             => 'Parse Error',
				E_COMPILE_ERROR     => 'Compile Error',
				E_COMPILE_WARNING   => 'Compile Warning',
				E_NOTICE            => 'Notice',
				E_WARNING           => 'Warning',
				E_RECOVERABLE_ERROR => 'Recoverable Error',
				E_DEPRECATED        => 'Deprecated',
				E_USER_NOTICE       => 'Notice',
				E_USER_WARNING      => 'Warning',
				E_USER_ERROR        => 'Error',
				E_USER_DEPRECATED   => 'Deprecated',
			];

			return in_array($code, array_keys($codes)) ? $codes[$code] : 'ErrorException';
		}

		return $exception::class;
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(Throwable $exception): mixed
	{
		$alert = (new Alert($this->output))->render(
			"<bold>{$this->escape($this->determineExceptionType($exception))}</bold> [ {$exception->getCode()} ]",
			Alert::DANGER
		);

		$info = "<red><bold>{$this->escape($exception->getMessage())}</bold></red>";

		if (!empty($exception->getFile())) {
			$info .= PHP_EOL
			. PHP_EOL
			. "The error occured in <bold>{$this->escape($exception->getFile())}</bold>"
			. " on line <bold>{$exception->getLine()}</bold>"
			. PHP_EOL;
		}

		$trace = $this->escape($exception->getTraceAsString());

		$this->output->errorLn($alert . PHP_EOL . $info . PHP_EOL . $trace);

		return false;
	}
}
