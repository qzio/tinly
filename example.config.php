<?php if (!defined('BASE_PATH')) die('You can not access this directly');
// comment block {{{
/* vim: set expandtab tabstop=3 shiftwidth=3: */
// +--------------------------------------------------------+   
// | PHP Version 5.x                                        |
// +--------------------------------------------------------+
// | Filename: example.config.php                           |
// +--------------------------------------------------------+
// | Copyright (c) 2008 Joel Hansson                        |
// +--------------------------------------------------------+
// | License: MIT                                           |
// +--------------------------------------------------------+
// | Author:   Joel Hansson <joel.hansson@gmail.com>        |
// +--------------------------------------------------------+
//
// }}} 

class Config
{
	public static $DB_HOST		   = 'localhost';
	public static $DB_USER			= 'root';
   public static $DB_PASSWORD     = '';
   public static $DB_DATABASE     = 'my_database';
   public static $DB_MANAGER      = 'mysql';
   public static $DB_DSN          = '';

	public static $TEMPLATE_DIR	= 'app/views/';
   public static $TIMEZONE       = 'Europe/Stockholm';
   public static $LOCAL          = 'sv_SE';

   public static $DEBUG          = true;
   public static $DISPLAY_ERRORS = 1;
   public static $ERROR_REP      = E_ALL;


   // static setDefaults() {{{
   public static function setDefaults()
   {
      Config::setDSN('development');
   } // }}}
   // static setDSN() {{{
   public static function setDSN($mode = 'development')
   {
      $dbname = ($mode == 'test') ? self::$DB_DATABASE_TEST : self::$DB_DATABASE;
      self::$DB_DSN = self::$DB_MANAGER.':dbname='.$dbname.';'.
         'host='.self::$DB_HOST;
   } // }}}
}
// set default values
Config::setDefaults();
?>
