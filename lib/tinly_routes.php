<?php
class Tinly_routes
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
	// getParams() {{{
	public function getParams()
	{
		// if post is not empty, return it
		if (!empty($_POST)) return $_POST;

		// else return get without the q
		$g = $_GET;
		$g['q'] = null;
		unset($g['q']);
		if (empty($g['id']) && !empty($this->u[2])) {
		  	$g['id'] = $this->u[2];
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
