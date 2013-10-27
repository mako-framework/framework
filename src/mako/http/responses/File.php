<?php

namespace mako\http\responses;

use \RuntimeException;
use \mako\http\Response;
use \mako\utility\File as FileUtility;

/**
 * File response.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

Class File implements \mako\http\responses\ResponseContainerInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * File path.
	 * 
	 * @var string
	 */

	protected $file;

	/**
	 * Options.
	 * 
	 * @var array
	 */

	protected $options;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $file     File path
	 * @param   array   $options  Options
	 */

	public function __construct($file, array $options = array())
	{
		if(file_exists($file) === false || is_readable($file) === false)
		{
			throw new RuntimeException(vsprintf("%s(): File [ %s ] is not readable.", array(__METHOD__, $file)));
		}

		$this->file = $file;

		$this->options = $options + array
		(
			'file_name'    => basename($file),
			'disposition'  => 'attachment',
			'content_type' => FileUtility::mime($file) ?: 'application/octet-stream',
		);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Sends the file.
	 * 
	 * @access  protected
	 */

	protected function sendFile()
	{
		// Erase output buffers and disable output buffering

		while(ob_get_level() > 0) ob_end_clean();

		// Send the file

		$handle = fopen($this->file, 'rb');

		while(!feof($handle) && !connection_aborted())
		{
			echo fread($handle, 4096);

			flush();
		}
 
		fclose($handle);
	}

	/**
	 * Sends the response.
	 * 
	 * @access  public
	 */

	public function send(Response $response)
	{
		$response->type($this->options['content_type']);

		$response->header('content-length', filesize($this->file));

		$response->header('content-disposition', $this->options['disposition'] . '; filename="' . $this->options['file_name'] . '"');

		$response->sendHeaders();

		$this->sendFile();
	}
}

/** -------------------- End of file -------------------- **/