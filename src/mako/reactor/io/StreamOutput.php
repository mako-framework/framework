<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\reactor\io;

use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\StreamOutput as SymfonyStreamOutput;

/**
 * Abstract stream output.
 *
 * @author  Frederic G. Østby
 */

abstract class StreamOutput extends SymfonyStreamOutput
{
	/**
     * Constructor.
     *
     * @access  public
     * @param   mixed  $stream  A stream resource
     */

	public function __construct($stream)
	{
		parent::__construct($stream);

		// Add some additional styles

		$this->getFormatter()->setStyle('black', new OutputFormatterStyle('black'));
		$this->getFormatter()->setStyle('red', new OutputFormatterStyle('red'));
		$this->getFormatter()->setStyle('green', new OutputFormatterStyle('green'));
		$this->getFormatter()->setStyle('yellow', new OutputFormatterStyle('yellow'));
		$this->getFormatter()->setStyle('blue', new OutputFormatterStyle('blue'));
		$this->getFormatter()->setStyle('magenta', new OutputFormatterStyle('magenta'));
		$this->getFormatter()->setStyle('cyan', new OutputFormatterStyle('cyan'));
		$this->getFormatter()->setStyle('white', new OutputFormatterStyle('white'));
	}

	/**
	 * Outputs n empty lines.
	 * 
	 * @access  public
	 * @param   int     $count  Number of empty lines
	 */

	public function nl($count = 1)
	{
		$this->write(str_repeat(PHP_EOL, $count));
	}

	/**
	 * Outputs a table.
	 * 
	 * @access  public
	 * @param   array   $headers  Table headers
	 * @param   array   $rows     Table rows
	 * @param   int     $layout   Table layout
	 */

	public function table(array $headers, array $rows, $layout = TableHelper::LAYOUT_DEFAULT)
	{
		$table = new TableHelper();

		$table->setLayout($layout);

		$table->setHeaders($headers);

		$table->setRows($rows);

		$table->render($this);
	}
}