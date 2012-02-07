<?php

namespace mako\controller;

use \mako\View as Vista;

/**
* View controller.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

abstract class View extends \mako\Controller
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* View instance.
	*
	* @var  mako\View
	*/

	protected $view;

	/**
	* View name.
	*
	* @var string
	*/

	protected $viewName;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	* Constructor.
	*
	* @access  public
	* @param   mako\Request   A request object
	* @param   mako\Response  A response object
	*/

	public function __construct($request, $response)
	{
		parent::__construct($request, $response);

		$this->view = new Vista($this->viewName);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	* This method runs after the action.
	*
	* @access  public
	*/

	public function after()
	{
		$this->response->body($this->view);
	}
}

/** -------------------- End of file --------------------**/