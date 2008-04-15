<?php
// comment block {{{
/* vim: set expandtab tabstop=3 shiftwidth=3: */
// +--------------------------------------------------------+   
// | PHP Version 5.x                                        |
// +--------------------------------------------------------+
// | Filename: lib/tinylog.php		                        |
// +--------------------------------------------------------+
// | Copyright (c) 2008 Joel Hansson                        |
// +--------------------------------------------------------+
// | License: MIT                                           |
// +--------------------------------------------------------+
// | Author:   Joel Hansson <joel@gottfolk.se>              |
// +--------------------------------------------------------+
//
//  }}}
class TinyLog
{
	// properties {{{
	protected $log_file;
	protected $handle;
	protected $error;
	// }}}
	// __construct($log_file) {{{
	public function __construct($log_file)
	{
		$this->suffix = '';
		$this->error = '';
		$this->setHandle($log_file);
	}
	// }}}
	// __desctruct() {{{
	public function __destruct()
	{
		@fclose($this->handle);
	}
	// }}}
	// doLog() {{{
	public function doLog($msg)
	{
		if (empty($msg)) return false;
		$from = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : 'local';
		$str = date('Y-m-d H:i').' from('.$from.'): '.$msg.$this->suffix."\n";
		@fwrite($this->handle,$str);
	}
	// }}}
	// setHandle() {{{
	protected function setHandle($log_file)
	{
		$this->log_file = $log_file;
		$this->handle = @fopen($this->log_file,'a');

		if ($this->handle === false) {
			$this->error = 'unable to open file '.$this->log_file;
		} 
	}
	// }}}
	// getError() {{{
	public function getError()
	{
		return $this->error;
	}
	// }}}
	// setSuffix() {{{
	public function setSuffix($str = '')
	{
		$this->suffix = ' '.$str;
	}
	// }}}
}
?>
