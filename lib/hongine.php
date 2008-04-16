<?php
// document block {{{
/* vim: set expandtab tabstop=3 shiftwidth=3: */
// +--------------------------------------------------------+   
// | PHP Version 5.x                                        |
// +--------------------------------------------------------+
// | Filename: lib/hongine.php                              |
// +--------------------------------------------------------+
// | Copyright (c) 2008 Joel Hansson                        |
// +--------------------------------------------------------+
// | License: MIT                                           |
// +--------------------------------------------------------+
// | Author:   Joel Hansson <joel.hansson@gmail.com>              |
// +--------------------------------------------------------+
//
// }}}
class hongine {
   protected $displayed;
   protected $tplDir;
   protected $template;
	protected $no_layout = false;
   // __construct {{{
   public function __construct(&$sess,$p = array())
   {

      $this->displayed = false;
      $this->tplDir = Config::$TEMPLATE_DIR;
      $this->vars['_controller'] = !empty($p['controller']) ? $p['controller'] : '';
      if (isset($sess['user_id'])) {
         $this->assign(array(
            'auth' => isset($sess['auth']) ? $sess['auth'] : 0,
            'inside' => true,
            'user_id' => $sess['user_id'],
         ));

      } else {
         $this->assign(array(
            'auth' => 0,
            'inside' => false
         ));
      }

      if (isset($sess['message'])) {
         $this->assign('message',$sess['message']);
         unset($sess['message']);
      }

      // load html helpers
      $this->html = new Html_Plugin(array('controller' => $this->vars['_controller']));
   }

   // }}}
   // assign {{{
   /**
    * assign a value to a variable
    *
    * @param string key    name of the variable
    * @param string/array  value of the variable
    */
   public function assign($key, $val = '')
   {

      // if we wish to set multiple vars.
      if (is_array($key)) {
         foreach($key as $k => $v) {
            if (!isset($this->$k)) {
               $this->$k = $v;
               $this->vars[$k] = $v;
            }
         }
         return true;
      }

      // we are not allowed to reset a variable
      if (isset($this->$key)) return false;
      // insufficient args
      if ( empty($key) ) return false;

      $this->$key = $val;
      $this->vars[$key] = $val;
      return true;
   }

   // }}}
   // display {{{
   /**
    * fetch and display the template
    *
    * @param string tplFile   The template file
    */
   public function display($tplFile = '',$controller = '')
   {

      // default not to use editors
      if (!isset($this->use_editors)) $this->use_editors = false;

      $tplFile = (empty($controller)) ? $tplFile : $controller.'/'.$tplFile;
      $tplFile = strtolower($tplFile);

      $this->template = $this->tplDir.$tplFile;

      // sanity check
      if (! is_file($this->template) ) {
         if (! is_file($this->template.'.tpl.php') ) {
            echo 'is not a file:'.$this->template;
            return false;
         } else {
            $this->template .= '.tpl.php';
         }
      } else {
         die('please dont');
      }

		$layout = $this->tplDir.'layout.tpl.php';
      // load our variables! :)
      extract($this->vars);

		$html = &$this->html;

      ob_start();

			if ($this->no_layout || !is_file($layout)) {
				require $this->template;
			} else {
				require($layout);
			}

      ob_end_flush();
      $this->displayed = true;
      return true;
   }

   // }}}
   //assignEmptyVars {{{
   public function assignEmptyVars($vars = array())
   {
      foreach($vars as $v) {
         $this->$v = '';
      }
   }
   // }}}
   // isDisplayed {{{
   public function isDisplayed()
   {
      return $this->displayed;
   }
   // }}}
	// yield {{{
	public function yield()
	{
      extract($this->vars);
		$html = &$this->html;
		require_once($this->template);
	}
   // }}}
   // dontUseLayout {{{
	public function dontUseLayout()
	{
		$this->no_layout = true;
	}
   //}}}
   // partial($name,$vars) {{{
   protected function partial($name,$vars = array())
   {
      $view_dir = dirname($this->template).'/';
      $tpl_file = $view_dir.'_'.$name.'.tpl.php';
      if (is_file($tpl_file)) {
         extract($vars);
		   $html = &$this->html;
         require $tpl_file;
      } else {
         die('fsck'.$tpl_file);
      }
   }
   // }}}
}
?>
