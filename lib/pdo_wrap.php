<?php
// comment block {{{
/* vim: set expandtab tabstop=3 shiftwidth=3: */
// +--------------------------------------------------------+   
// | PHP Version 5.x                                        |
// +--------------------------------------------------------+
// | Filename: pdo.childclass.inc                           |
// +--------------------------------------------------------+
// | Copyright (c) 2008 Joel Hansson                        |
// +--------------------------------------------------------+
// | License: MIT                                           |
// +--------------------------------------------------------+
// | Author:   Joel Hansson <joel.hansson@gmail.com>              |
// +--------------------------------------------------------+
//
//  }}}


class pdo_wrap extends PDO {
   // Properties {{{
   /**
    * Database connect resource #id
    * @var num
    */
   protected   $linkId;

   /**
    * Keeps track of query amount
    * @var num
    */
   public static  $qc = 0;

   /**
    * An array with all present tablesa
    * @var array
    */
   protected   $table_list;

   private static $instance;

   protected   $my_name;
   // }}}
   // __construct {{{
   /**
    * Initiate database connect
    *
    * @param string $dsn      the dsn used
    * @param string $username the username
    * @param string $passwd   the password
    * @param string $dbName   the name of the database
    */
   public function __construct($dsn, $user, $passwd)
   {
      parent::__construct($dsn,$user,$passwd);

	   $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      // Shitty php5.x forces me to ugly code *ARGH*
      register_shutdown_function(array(&$this,'__shutdown'));
   }

   // }}}
   // getInstance() {{{
   public static function getInstance($dsn = '',$user= '',$passwd='')
   {
      if (!isset(self::$instance)) {
         $c = __CLASS__;
         self::$instance = new $c($dsn,$user,$passwd);
      }
      return self::$instance;
   }
   // }}}
   // pexec {{{
   public function pexec($sql, $params = array())
   {
      try {
         $result = $this->exec($sql);
         if (isset($params['debug']) && $params['debug'] == true) {
            echo "THE MYSQL_ERROR (". mysql_error().")<br/>";
            echo "the sql: (".$sql.")<br/>";
            $h = new Html_Plugin();
            echo $h->p($this->errorInfo());
            echo "<br/>";
            echo "Result of the exec: (".$result.")<br/>";
         }
      } catch(PDOException $e) {
         $result = false;
         $this->pcatch($e);
      }
      self::$qc++;
      return $result;
   }

   // }}}
   // pfetchAll($sql,$class='') {{{
   public function pfetchAll($sql,$class ='')
   {
      // result is per default an empty array
      $result = array();
      try {
         $stmt = $this->prepare($sql);
         $stmt->execute();
         
         // dont use PDO:FETCH_CLASS it sets attrs by $obj->$field = $value;...
         //$result = (!empty($class)) ? $stmt->fetchAll(PDO::FETCH_CLASS,$class) : $stmt->fetchAll(PDO::FETCH_ASSOC);
         $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
         if (!empty($class)) {
            foreach($res as $r) {
               $result[] = new $class($r);
            }
         } else $result = $res;

         $stmt->closeCursor();
      } catch(PDOException $e) {
         $result = false;
         $this->pcatch($e);
      }
      self::$qc++;
      $stmt = null;
      return is_array($result) ? $result : array();
   }

   // }}}
   // pfetch($sql,$class = '') {{{
   public function pfetch($sql,$class = '')
   {
      try {
         if (! $stmt = $this->prepare($sql)) {
            throw new PDOException();
         }

         $stmt->execute();
         $res = $stmt->fetch(PDO::FETCH_ASSOC);
         if (!empty($class)) {
            $result = new $class($res);
         } else $result = $res;

      } catch(PDOException $e) {
         $result = false;
         $this->pcatch($e);
      }
      self::$qc++;
      $stmt = null;
      return $result;
   }

   // }}}
   // pcatch {{{
   public function pcatch($e)
   {
      if (Config::$DEBUG == false) {
         // do some log stuff;
         die();
      }
      $content =  '<div class="phperror" style="text-align:left;">';
      $h = new Html_Plugin();
      $content .= $h->p($e->getTrace());
      $content .= '</div>';

      echo 'Failed SQL: '.$e->getMessage().'<br/><br/>';
      Html_Plugin::toggleBox('debug backtrace',$content);
      return false;
   }


   // }}}
   // pexistTable {{{
   public function pexistTable($tbl)
   {
      if (! empty($this->table_list) ) {
         return (in_array($tbl,$this->table_list)) ? true : false;
      }

      if ($tpls = $this->pfetchAll('show tables')) {
         foreach($tpls as $key => $val) {
            $this->table_list[] = current($val);
         }
         return (in_array($tbl,$this->table_list)) ? true : false;
      }

      return false; // this should not be possible..
   }

   // }}}
   // getCounter {{{
   public static function getCounter()
   {
      return self::$qc;
   }

   // }}}
   // squery($sql,$params = array()) wrapper for sexec {{{
   public function squery($sql,$params =array())
   {
      return $this->sexec($sql,$params);
   }
   // }}}
   // sexec(sql,$params) {{{
   public function sexec($sql,$params = array())
   {
      $result = false;
      $params = (!empty($params)) ? $params : array();
      try {
         $stmt = $this->prepare($sql);
         if ($stmt->execute($params)) {
            $result = true;
         }
         $stmt->closeCursor();

      } catch(PDOException $e) {
         $result = false;
         $this->pcatch($e);
      }
      self::$qc++;
      return $result;
   }
   // }}}
   // sfetchAll($sql,$params = array()) {{{
   public function sfetchAll($sql,$params = array())
   {
      $result = false;
      $params = (!empty($params)) ? $params : array();
      try {
         $stmt = $this->prepare($sql);
         $stmt->execute($params);
         $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $stmt->closeCursor();

      } catch(PDOException $e) {
         $this->pcatch($e);
      }
      self::$qc++;
      return $result;
   }

   // }}}
   // __shutdown {{{
   public function __shutdown()
   {
      session_write_close($this);
      return true;
   }
   // }}}
   // __clone() {{{
   public function __clone()
   {
      trigger_error('Clone is not allowed.', E_USER_ERROR);
   }// }}}
}
