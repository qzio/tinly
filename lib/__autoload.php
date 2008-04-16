<?php
// comment block {{{
/* vim: set expandtab tabstop=3 shiftwidth=3: */
// +--------------------------------------------------------+   
// | PHP Version 5.x                                        |
// +--------------------------------------------------------+
// | Filename: lib/__autoload.php                           |
// +--------------------------------------------------------+
// | Copyright (c) 2008 Joel Hansson                        |
// +--------------------------------------------------------+
// | License: MIT                                           |
// +--------------------------------------------------------+
// | Author:   Joel Hansson <joel.hansson@gmail.com>        |
// +--------------------------------------------------------+
//
//  }}}
function __autoload($class)
{
   $class = strtolower($class);
   $class_suffix = class_suffix($class);

   // if we dont have a class suffix, then it might be in the lib directory
   // or it might even be the config class!
   if ($class_suffix === false) {
      if ($class == 'config') {
         require_once 'config.php';
      } else {
         inc_class($class);
      }
   } else {
      inc_class($class,$class_suffix);
   }
}

// get class suffix
function class_suffix($classname)
{
   //tinly controller is just a parent...
   if ($classname == 'tinly_controller') return false;
   $pos = strrpos($classname,'_');
   if ($pos === false) return false;
   $class_suffix = strtolower(substr($classname,$pos+1));

   switch ($class_suffix) {
   case 'controller':
      break;
   case 'plugin':
      break;
   default:
      return false;
      break;
   }
   return $class_suffix;
}

// include the class file.
function inc_class($class,$type = '')
{
   $class_file = $class.'.php';
   if (empty($type)) {
      if (file_exists('lib/'.$class_file)) {
         $class_file = 'lib/'.$class_file;
      } else if (file_exists('app/models/'.$class_file)) {
         $class_file = 'app/models/'.$class_file;
      } else if (is_dir(BASE_PATH.'vendors/')) {
         $vhandle = opendir(BASE_PATH.'vendors/');
         while(false !== ($ent = readdir($vhandle))) {
            if (is_dir($ent)) {
               $f = BASE_PATH.'vendors/'.$ent.'/'.$class_file;
               if (file_exists($f) ) {
                  $class_file = $f;
               }
            }
         }
      }

   } else if ($type == 'plugin') {
      $class_file = 'plugins/'.$class_file;
   } else if($type == 'controller') {
      $class_file = 'app/controllers/'.$class_file;
   }

   if (file_exists($class_file)) {
      require_once $class_file;
   } else {
      die('<br/>unable to include requested class '.$class.' ('.$class_file.') type ('.$type.')dying...');
   }
}


// fix magic qoutes
if (get_magic_quotes_gpc()) {
   function undoMagicQuotes($array, $topLevel=true) {
      $newArray = array();
      foreach($array as $key => $value) {
         if (!$topLevel) {
            $key = stripslashes($key);
         }
         if (is_array($value)) {
            $newArray[$key] = undoMagicQuotes($value, false);
         }
         else {
            $newArray[$key] = stripslashes($value);
         }
      }
      return $newArray;
   }
   $_GET = undoMagicQuotes($_GET);
   $_POST = undoMagicQuotes($_POST);
   $_COOKIE = undoMagicQuotes($_COOKIE);
   $_REQUEST = undoMagicQuotes($_REQUEST);
}

// add custom error handling
//require_once 'lib/errorhandler.php';
