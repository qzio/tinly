<?php
// comment block {{{
/* vim: set expandtab tabstop=3 shiftwidth=3: */
// +--------------------------------------------------------+   
// | PHP Version 5.x                                        |
// +--------------------------------------------------------+
// | Filename: tinly_controller.inc                         |
// +--------------------------------------------------------+
// | Copyright (c) 2008 Joel Hansson                        |
// +--------------------------------------------------------+
// | License: MIT                                           |
// +--------------------------------------------------------+
// | Author:   Joel Hansson <joel.hansson@gmail.com>              |
// +--------------------------------------------------------+
//
// }}}
class tinly_controller {
   // properties {{{
   protected $_get = array(); 
   protected $_post = array();
   protected $_files = array();
   protected $params = array();
   protected $request = 'get';
   // }}}
   //__construct {{{
   public function __construct()
   {
      $this->initiateBase();
   }
   // }}}
   //initiateBase {{{
   public function initiateBase()
   {
      $this->my_name = substr(get_class($this),0,-11);
      $this->tpl = new tinly_template($_SESSION,array('controller' => $this->my_name)); 

      // set _get,_post,_files as class variables.
      $this->_get = $_GET;
      $this->_post = $_POST;
      $this->_files = $_FILES;
   }
   // }}}
   //auth {{{
   public function auth($level = 1,$return = false)
   {
      if (isset($_SESSION['auth']) && $_SESSION['auth'] >= $level)
         return true;
      else {
         if ($return) return false;
         sendMsg('/',textid('system/messages/no_permission'));
      }
      die('foo');
   }
   // }}}
   //fallback {{{
   public function fallback() {
      echo "unable to handle the url";
      Html_plugin::p($_GET);
      die();
   }
   // }}}
   // setParam($key,$val) {{{
   public function setParams($key,$val = '')
   {
      if (is_array($key)) {
         foreach($key as $k => $v) {
            $this->setParams($k,$v);
         }
      } else {
         $this->params[$key] = $val;
      }
   } // }}}
   //__call {{{
   public function __call($action,$args = array()) 
   {
      $method = $action.'_';
      if (method_exists($this,$method)) {
         $this->$method();
         if (!$this->tpl->isDisplayed()) {
            $this->tpl->display($action,$this->my_name);
         }
      } else {
         echo "method ".$method." doesnt exists in ".$this->my_name.' class';
         $this->fallback();
      }
   }
   // }}}
   //load {{{
   public function load($p = array())
   {
      if (is_string($p)) $this->$p = new $p;

      if (is_array($p) && !empty($p)) {
         foreach($p as $model) {
            $this->$model = new $model();
         }
      }
   }
   // }}}
   // redirect($uri,$msg) {{{
   public function redirect($uri,$msg = '') 
   {
      $_SESSION['message'] = $msg;
      header('Location: '.$uri);
   } // }}}
   // setRequest($req) {{{
   public function setRequest($req)
   {
      $this->request = $req;
   }
   // }}}
   // parseQuery($uri_segment) {{{
   public function parseQuery($uri_segment)
   {
      $r = array();
      if (!empty($uri_segment[2])) {
         $r = array('id' => $uri_segment[2]);
      }
      return $r;
   }
   // }}}
}
