<?php
/* vim: set expandtab tabstop=3 shiftwidth=3: */
// +--------------------------------------------------------+   
// | PHP Version 5.x                                        |
// +--------------------------------------------------------+
// | Filename: qerror.inc                                   |
// +--------------------------------------------------------+
// | Copyright (c) 2008 Joel Hansson                        |
// +--------------------------------------------------------+
// | License: MIT                                           |
// +--------------------------------------------------------+
// | Author:   Joel Hansson <joel.hansson@gmail.com>              |
// +--------------------------------------------------------+
// 
//

$errors = array();

function errorHandler($errno, $errstr, $errfile, $errline) 
{
   $errortype = array (
         E_ERROR           => "Error",
         E_WARNING         => "Warning",
         E_PARSE           => "Parsing Error",
         E_NOTICE          => "Notice",
         E_CORE_ERROR      => "Core Error",
         E_CORE_WARNING    => "Core Warning",
         E_COMPILE_ERROR   => "Compile Error",
         E_COMPILE_WARNING => "Compile Warning",
         E_USER_ERROR      => "User Error",
         E_USER_WARNING    => "User Warning",
         E_USER_NOTICE     => "User Notice",
         E_STRICT          => "Runtime Notice",
   );

   // if a function etc, where called with an prefix of @ do nothing
   if (CONFIG::$DEBUG !== false) {
      displayError($errortype[$errno].', '.$errstr, $errfile, $errline);
   }
}

function displayError($errstr, $errfile, $errline)
{
   ?>
   <div class="phperror">
      <p><?php echo $errstr;?></p>
      <p>file: <?php echo $errfile; ?> line <?php echo $errline;?></p>
      <?php Html_Plugin::toggleBox('backtrace',Html_Plugin::p(debug_backtrace())) ?>
   </div>
   <?php
}
set_error_handler('errorHandler');
?>
