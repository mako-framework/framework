<?php

namespace mako
{
	use \mako\Request;
	use \mako\Response;
	
	/**
	* Base controller that all application controllers must extend.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2012 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	abstract class Controller
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		/**
		* Holds the request object that loaded the controller.
		*/

		protected $request;
		
		/**
		* Holds singleton instance of the response object.
		*/
		
		protected $reponse;

		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------

		/**
		* Constructor.
		*
		* @access  public
		* @param   Request   A request object
		* @param   Response  A response object
		*/

		public function __construct(Request $request, Response $response)
		{
			$this->request  = $request;
			$this->response = $response;
		}

		//---------------------------------------------
		// Class methods
		//---------------------------------------------

		/**
		* Index method must always be included.
		*
		* @access  public
		*/

		abstract public function _index();

		/**
		* This method runs before the action.
		*
		* @access  public
		*/

		public function _before()
		{

		}

		/**
		* This method runs after the action.
		*
		* @access  public
		*/

		public function _after()
		{

		}
	}	
}

/** -------------------- End of file --------------------**/