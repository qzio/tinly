<?php
// commenet block {{{
/* vim: set expandtab tabstop=3 shiftwidth=3: */
// +--------------------------------------------------------+   
// | PHP Version 5.x                                        |
// +--------------------------------------------------------+
// | Filename: lib/tinly_model.php                          |
// +--------------------------------------------------------+
// | Copyright (c) 2008 Joel Hansson                        |
// +--------------------------------------------------------+
// | License: MIT                                           |
// +--------------------------------------------------------+
// | Author:   Joel Hansson <joel.hansson@gmail.com>        |
// +--------------------------------------------------------+
//
//  }}}

class tinly_model {
   // properties {{{
   //public $quote_types = array('varchar', 'datetime','tinytext','text','longtext','tinyblob','blob','longblob');
   public $tbl = 'base_model_table';
   public $fields = array();
   public $pkey = 'id';
   public $posts_per_page = 20;
   public $files = array();
   public $pdo;
   private static $instance;
   protected $attr = array();
   protected $errors = array();
   // }}}
   
   //__construct {{{
   public function __construct($p = array())
   {
      if (!is_object($this->pdo)) {
         $this->pdo = PDO_Wrap::getInstance(Config::$DB_DSN,Config::$DB_USER,Config::$DB_PASSWORD);
         //$this->pdo = &$GLOBALS['pdo'];
      }
      if ( is_array($p) && (!empty($p) || !empty($p['files'])) ) {
         $this->setAttributes($p);
      }
   }
   // }}}
   // getInstance() {{{
   public static function getInstance()
   {
      if (!isset(self::$instance)) {
         $c = __CLASS__;
         self::$instance = new $c();
      }
      return self::$instance;
   }
   // }}}
   
   // public =====================
   //getErrors {{{
   public function getErrors() 
   {
      return $this->errors;
   }
   // }}}
   // getAttr {{{
   public function getAttr()
   {
      return $this->attr;
   }
   // }}}
   
   // setters
   //setPostsPerPage {{{
   public function setPostsPerPage($num = 20)
   {
      $this->posts_per_page = intval($num);
   }
   // }}}
   //setAttributes {{{
   public function setAttributes($p,$_f = array()) {
      /*if (!is_object($this->pdo)) {
         $this->pdo = PDO_Wrap::singleton(Config::$DB_DSN,Config::$DB_USER,Config::$DB_PASSWORD);
      }*/
      foreach($this->fields as $f => $a) {
         if (array_key_exists($f,$p)) {
            $p[$f] = ($a['type'] == 'int') ? intval($p[$f]) : $p[$f];
            $p[$f] = ($a['type'] == 'bigint') ? intval($p[$f]) : $p[$f];
            $p[$f] = ($a['type'] == 'float') ? floatval($p[$f]) : $p[$f];
            $this->attr[$f] = $p[$f];
         } 
      }
      $this->files = $_f;
   }
   // }}}
   // force_attr($key,$val) {{{
   // man you should know what you're doing!
   public function force_attr($key,$val)
   {
      $this->attr[$key] = $val;
   }
   // }}}
   // method overriding
   // __get (dynamic) {{{
   public function __get($var)
   {
      if (!isset($this->attr[$var])) return false;
      $r = $this->attr[$var];

      /*
      // some onquote stuff...
      if (in_array($this->fields[$var]['type'],$this->quote_types)) {
         $r = (substr($r,0,1) == "'" && substr($r,-1,1) == "'") ? substr($r,1,-1) : $r;
      }
      */
      return $r;
   }
   // }}}
   // __set (dynamic) {{{
   public function __set($key = '', $val = '')
   {
      if ($key == 'errors') $this->errors = $val;
      else $this->setAttributes(array($key => $val));
      return true;
   }
   // }}}
   
   // DB access
   //save($p = array()) {{{
   public function save($p = array())
   {
      // if there is a validate functions. run it.
      if (method_exists($this,'Validate')) {

         // return false on validation errors
         if (!$this->Validate($this->attr,$this->files)) {
            return false;
         }
      }

      // return false on empty attr array.
      if (count($this->attr) < 1) return false;

      // perform before_save functions.
      $this->before_save();

      // containes a column set string and values

      // update post if primary key is set, or force_insert param is not set.
      if (isset($this->attr[$this->pkey]) && !isset($p['force_insert'])) {
         $prepares = $this->prepareSet('update');
         $r = $this->update($prepares);

      // else do insert
      } else {
         $prepares = $this->prepareSet();
         $r = $this->insert($prepares);
      }
      return $r;
   } // }}}
   //remove($p)  {{{
   public function remove($pvalue)
   {
      $r = false;
      try {
         $sql = 'delete from '.$this->tbl.' where '.$this->pkey.' = ?';
         $stmt = $this->pdo->prepare($sql);
         $r = $stmt->execute(array($pvalue));
         $this->pdo->qc++;
      } catch(PDOException $e) {
         $this->pdo->pcatch($e);
      }
      return $r;
   } // }}}
   //fetchList() {{{
   public function fetchList($sql='',$params = array())
   {
      $sql = !empty($sql) ? $sql : 'select * from '.$this->tbl;
      return $this->pdo->sfetchAll($sql,$params);
   } // }}}
   //fetchOne($pvalue) {{{
   public function fetchOne($pvalue)
   {
      $sql = 'select * from '.$this->tbl.' where '.$this->pkey.' = ? limit 1';
      $result = $this->pdo->sfetchAll($sql,array($pvalue));
      return $result[0];
   } // }}}
   //countPosts($where = '') {{{
   public function countPosts($where = '')
   {
      $where = !empty($where) ? 'where '.$where.' ' : '';

      $sql = 'select count(*) as sum from '.$this->tbl.' '.
         $where;
      $r = $this->pdo->pfetch($sql);
      return (!empty($r)) ? $r['sum'] : '0';
   } // }}}
   
   // protected ==================
   // before_save() {{{
   protected function before_save()
   {
      foreach($this->fields as $f => $a) {
         if (array_key_exists($f,$this->attr) && !empty($a['before_save'])) {

            $this->attr[$f] = call_user_func(
               array($this,$a['before_save']),$this->attr[$f]
            );
         }
      }
   } // }}}
   // insert($prepares) {{{
   protected function insert($prepares)
   {
      $r = false;
      try {
         $sql = 'insert into `'.$this->tbl.'` set ';
         $sql .= $prepares['column_str'];
         $values = $prepares['values'];
         $stmt = $this->pdo->prepare($sql);
         // execute query
         if ($r = $stmt->execute($values)) {
            $this->attr[$this->pkey] = $this->pdo->lastInsertId();
         }
         $this->pdo->qc++;

      } catch(PDOException $e) {
         $this->pdo->pcatch($e);
      }
      return $r;
   } // }}}
   // update($prepares) {{{
   protected function update($prepares)
   {
      $r = false;
      try {
         // prepare sql and values
         $sql = 'update `'.$this->tbl.'` set '.$prepares['column_str'];

         // set where clause
         if (array_key_exists($this->pkey,$this->attr)) {
            $sql .= ' where `'.$this->pkey.'` = :'.$this->pkey;
            $prepares['values'][':'.$this->pkey] = $this->attr[$this->pkey];
         } 
         $stmt = $this->pdo->prepare($sql);
         foreach($prepares['values'] as $f => $v) {
            $stmt->bindParam($f,$prepare['values'][$f]);
         }
         $r = $stmt->execute();
         $this->pdo->qc++;

      } catch(PDOException $e) {
         $this->pdo->pcatch($e);
      }
      return $r;
   } // }}}
   // prepareSet() {{{
   protected function prepareSet()
   {
      $returns = array('column_str' => '','values' => array());

      $column_str = '';
      $values = array();
      // loop through the attributes
      foreach($this->attr as $f => $v) {
         
         // dont insert primary key or custom fields into db
         if ($f != $this->pkey && $this->fields[$f]['type'] != 'custom') {

            $column_str.= ', `'.$f.'` = ';
            // treat curdate special
            if ($v == 'curdate()') {
               $column_str .= 'curdate()';
            } else {
               $column_str .=':'.$f;
               // serialize arrays before inserting into database.
               $values[':'.$f] = (is_array($v)) ? serialize($v) : $v;
            }
         }
      }
      
      // trim away the trailing space and the comma.
      $returns['column_str'] = ltrim(ltrim($column_str),',');
      $returns['values'] = $values;
      return $returns;
   } // }}}
}
?>
