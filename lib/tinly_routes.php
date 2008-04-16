<?php
class tinly_routes
{
	// properties {{{
	protected $u = array();
	public $default_controller = 'test';
	public $default_action = 'index';

	// }}}
	// __construct($uri_segment) {{{
	public function __construct($uri_segment)
	{
		$this->u = $uri_segment;
	}
	// }}}
	// getController() {{{
	public function getController()
	{
		return (!empty($this->u[0])) ? $this->u[0] : $this->default_controller;
	} // }}}
	// getAction() {{{
	public function getAction()
	{
		return (!empty($this->u[1])) ? $this->u[1] : $this->default_action;
	} // }}}
	// getParams($controller = '') {{{
	public function getParams($controller = '')
	{
		// if post is not empty, return it
		if (!empty($_POST)) return $_POST;

		$g = array();
		if (is_object($controller)) {
			$g = $controller->parseQuery($this->u);
		}
		return $g;
	} // }}}
	// getRequest() {{{
	public function getRequest()
	{
		return (!empty($_POST)) ? 'post' : 'get';
	} // }}}
}
?>
