<?php
// commenet block {{{
/* vim: set expandtab tabstop=3 shiftwidth=3: */
// +--------------------------------------------------------+   
// | PHP Version 5.x                                        |
// +--------------------------------------------------------+
// | Filename: base_model.inc                               |
// +--------------------------------------------------------+
// | Copyright (c) 2008 Joel Hansson                        |
// +--------------------------------------------------------+
// | License: MIT                                           |
// +--------------------------------------------------------+
// | Author:   Joel Hansson <joel@gottfolk.se>              |
// +--------------------------------------------------------+
//
//  }}}
//
class Base_Model {
   // properties {{{
   public $quote_types = array('varchar', 'datetime','tinytext','text','longtext','tinyblob','blob','longblob');
   public $tbl = 'base_model_table';
   public $fields = array();
   public $pkey = 'id';
   public $posts_per_page = 20;
   protected $attr = array();
   protected $errors = array();
   public $files = array();
   public $pdo;
   private static $instance;
   // }}}
   
   //__construct {{{
   public function __construct($p = array())
   {
      if (!is_object($this->pdo)) {
         $this->pdo = PDO_Wrap::singleton(Config::$DB_DSN,Config::$DB_USER,Config::$DB_PASSWORD);
         //$this->pdo = &$GLOBALS['pdo'];
      }
      if ( is_array($p) && (!empty($p) || !empty($p['files'])) ) {
         $this->setAttributes($p);
      }
   }
   // }}}
   // singleton() {{{
   public static function singleton()
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
   // getPost {{{
   public function getPost()
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
      if (!is_object($this->pdo)) {
         $this->pdo = PDO_Wrap::singleton(Config::$DB_DSN,Config::$DB_USER,Config::$DB_PASSWORD);
      }


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

      // some onquote stuff...
      if (in_array($this->fields[$var]['type'],$this->quote_types)) {
         $r = (substr($r,0,1) == "'" && substr($r,-1,1) == "'") ? substr($r,1,-1) : $r;
      }
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

      // update post if primary key is set, or force_insert param is not set.
      if (isset($this->attr[$this->pkey]) && !isset($p['force_insert'])) {
         $r = $this->update($this->attr);

      // else do insert
      } else {
         $r = $this->insert($this->attr);
      }
      return $r;
   } // }}}
   //remove($p)  {{{
   public function remove($p = array())
   {
      if (is_string($p) || is_int($p)) {
         $sql = 'delete from '.$this->tbl.' where '.
            $this->pkey.' = '.$p;
      } elseif (is_array($p) && !empty($p['where'])) {
         $sql = 'delete from '.$this->tbl.' where '.$p['where'];
      }

      if (!empty($sql)) return $this->pdo->pexec($sql);
      return true;
   } // }}}
   //fetchList($p = array()) {{{
   public function fetchList($p = array())
   {
      if (isset($p['page'])) {
         $limit_start = intval($p['page']) * $this->posts_per_page;
         $p['limit'] = intval($limit_start).', '.$this->posts_per_page;
      }
      $fields = !empty($p['fields']) ? $p['fields'] : '*';
      $sql = 'select '.$fields.' from '.$this->tbl;
      if (!empty($p['where'])) {
         $sql .= ' where '.$p['where'];
      }
      $sql .= (!empty($p['order_by'])) ? ' order by '.$p['order_by'] : '';
      $sql .= (!empty($p['limit'])) ? ' limit '.$p['limit'] : '';
      $r = $this->pdo->pfetchAll($sql,get_class($this));
      return $r;

   } // }}}
   //fetchOne($p = array()) {{{
   public function fetchOne($p = array())
   {
      $fields = (is_array($p) && isset($p['fields'])) ? $p['fields'] : '*';
      if (is_string($p) || is_int($p)) {
         $key_value = $p;
      }
      $sql = 'select '.$fields.' from '.$this->tbl;
      if (is_array($p) && isset($p['where'])) {
         $sql .= ' where '.$p['where'];
      } elseif (isset($key_value)) {
         $sql .= ' where '.$this->pkey.' = '.$key_value;
      }
      $sql .= ' limit 1';
      if ($r = $this->pdo->pfetch($sql)) {
         $this_class = get_class($this);
         $obj = new $this_class($r);
         return $obj;
      } else {
         return array();
      }
   } // }}}
   //countPosts($where '') {{{
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
   // insert {{{
   protected function insert()
   {
      try {
         $sql = 'insert into `'.$this->tbl.'` set ';
         $prepares = $this->prepareSet();
         $sql .= $prepares['column_str'];
         $values = $prepares['values'];
         $stmt = $this->pdo->prepare($sql);

         // set values
         foreach($values as $f => $v) {
            $stmt->bindParam($f,$values[$f]);
         }

         // execute query
         $r = $stmt->execute();

         $this->pdo->query_count++;
         $this->attr[$this->pkey] = $this->pdo->lastInsertId();
         return $r;

      } catch(PDOException $e) {
         $this->pdo->pcatch($e);
      }
      return false;
   } // }}}
   // update() {{{
   protected function update()
   {
      try {
         // prepare sql and values
         $sql = 'update `'.$this->tbl.'` set ';

         $prepares = $this->prepareSet('update');
         $sql .= $prepares['column_str'];
         $values = $prepares['values'];

         // set where clause
         if (array_key_exists($this->pkey,$this->attr)) {
            $sql .= ' where `'.$this->pkey.'` = :'.$this->pkey;
            $values[':'.$this->pkey] = $this->attr[$this->pkey];
         } 
         $stmt = $this->pdo->prepare($sql);
         foreach($values as $f => $v) {
            $stmt->bindParam($f,$values[$f]);
         }
         $r = $stmt->execute();
         $this->pdo->query_count++;
         return $r;

      } catch(PDOException $e) {
         $this->pdo->pcatch($e);
      }
      return false;
   } // }}}
   // prepareSet() {{{
   protected function prepareSet($type = 'insert')
   {
      $returns = array('column_str' => '','values' => array());

      $column_str = '';
      $values = array();
      foreach($this->attr as $f => $v) {
         // dont insert custom fields into db.
         if($this->fields[$f]['type'] == 'custom') continue;

         if ($f != $this->pkey) {
            $column_str.= ', `'.$f.'` = ';
            if ($v == 'curdate()') {
               $column_str .= 'curdate()';
            } else {
               $column_str .=':'.$f;
               $values[':'.$f] = (is_array($v)) ? serialize($v) : $v;
            }
         }
      }
      
      // trim away the trailing space and the comma.
      $returns['column_str'] = ltrim(ltrim($column_str),',');
      $returns['values'] = $values;
      return $returns;
   }

   // }}}
}
?>
