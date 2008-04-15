<?php
// commenet block {{{
/* vim: set expandtab tabstop=3 shiftwidth=3: */
// +--------------------------------------------------------+   
// | PHP Version 5.x                                        |
// +--------------------------------------------------------+
// | Filename: core.php                                     |
// +--------------------------------------------------------+
// | Copyright (c) 2008 Joel Hansson                        |
// +--------------------------------------------------------+
// | License: MIT                                           |
// +--------------------------------------------------------+
// | Author:   Joel Hansson <joel@gottfolk.se>              |
// +--------------------------------------------------------+
//
//  }}}

class Core {
   static $instance;
   static $start_time;
   static $base_path; 
   protected $uri_segments;
   // __construct() {{{
   private function __construct()
   {
   }// }}}
   // singleton() {{{
   static function singleton()
   {
      if (!isset(self::$instance)) {
         $c = __CLASS__;
         self::$instance = new $c();
         /*
         self::$instance->setInitialVars();
         self::$instance->initiateController();
          */
      }
      return self::$instance;
   } // }}}
   // __clone() {{{
   public function __clone()
   {
      trigger_error('you cannot clone Core, its a singleton!');
   } // }}}
   // setInitialVars() {{{
   public function setInitialVars()
   {
      self::$start_time = microtime(true);
      self::$base_path = substr(dirname(realpath(__FILE__)),0,-3);
      ini_set('include_path',ini_get('include_path').':'.self::$base_path);
      define('BASE_PATH',self::$base_path);

      ini_set('display_errors',Config::$DISPLAY_ERRORS);
      error_reporting(Config::$ERROR_REP);
      setlocale(LC_ALL,Config::$LOCAL);

      $this->load('lib/errorhandler.php');

      if (function_exists('date_default_timezone_set')) {
         date_default_timezone_set(Config::$TIMEZONE);
      }

      // the database object
      //require_once 'lib/dbcon.php';
   } // }}}
   // uriSegment($num = 0) {{{
   public function uriSegment($num = 0)
   {
      if (!is_array($this->uri_segments)) {
         $this->uri_segments = (isset($_SERVER['QUERY_STRING'])) ? explode('/',$_SERVER['QUERY_STRING']) : array();
      }
      if ($num == 'whole') return $this->uri_segments;
      return (!empty($this->uri_segments[$num])) ? $this->uri_segments[$num] : false;
   } // }}}
   // initiateController() {{{
   public function initiateController()
   {
      if ($_SERVER['SCRIPT_FILENAME'] == './scripts/db.php') return false;

      $route = new Routes($this->uriSegment('whole'));
      $controller_class = $route->getController().'_controller';
      $action = $route->getAction();

      if (class_exists($controller_class) && !empty($action)) {
         $controller = new $controller_class;
         $controller->setParams($route->getParams());
         $controller->setRequest($route->getRequest());
         $controller->$action();
      } else {
         die("404 - dont know what you're looking for");
      }
   } // }}}
   // redirect($p = array()) {{{
   public static function redirect($p = array())
   {
      $controller = isset($p['controller']) ? $p['controller'] : '';
      $action = isset($p['action']) ? $p['action'] : '';
      $id = isset($p['id']) ? $p['id'] : '';
      $message = isset($p['message']) ? $p['message'] : '';
      $_SESSION['message'] = $message;
      //header('location: /?q='.$controller.'/'.$action);
      header('location: /'.$controller.'/'.$action.'/'.$id);
   } // }}}
   public static function load($params = array())
   {
      if (is_string($params)) {
         require_once $params;
         return false;
      }

      $type = $params['type'];
      $class = strtolower($params['class']).'.php';
      switch($type) {

         case 'plugin':
            require_once 'plugins/'.$class;
            break;
         default:
            require_once 'lib/'.$class;
            break;
      }

      return false;

   }
   // __desctruct() {{{
   public function __destruct()
   {
      if (CONFIG::$TEMPLATE_DIR == 'app/views/') {
         echo '<!-- generation time: '.sprintf('%0.4f',(microtime(true)-self::$start_time)).'s -->'."\n";
      }
   }// }}}
}
?>
