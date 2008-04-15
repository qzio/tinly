<?php
/* vim: set expandtab tabstop=3 shiftwidth=3: */
// +--------------------------------------------------------+   
// | PHP Version 5.x                                        |
// +--------------------------------------------------------+
// | Filename: validate.php                                 |
// +--------------------------------------------------------+
// | Copyright (c) 2008 Joel Hansson                        |
// +--------------------------------------------------------+
// | License: MIT                                           |
// +--------------------------------------------------------+
// | Author:   Joel Hansson <joel@gottfolk.se>              |
// +--------------------------------------------------------+
//
// 
//

class Validate {
   // Properties {{{
   /**
    * Associative array with error messages
    *
    * @var array
    */
   protected $error = array();

   /**
    * Associative array with strings
    * that are to be validated
    *
    * @var array
    */
   protected $str;

   // }}}
   // __construct {{{
   /**
    * constructor, set
    * values
    *
    * @param val array  associative array with all values
    */
   public function __construct($val)
   {
      $this->str = $val;
   }

   // }}}
   // char($id,$min,$max) {{{
   /**
    * Checks string for sane characters.
    * return true if the string is valid.
    * else return false and put a error mark in $error variable
    *
    * @param string id  identifier
    * @param int min    minimum amount of chars
    * @param int max    maximum amount of chars
    */
   public function char($id, $min, $max)
   {
      $str = $this->getStr($id);
      
      if (!preg_match('/^[\w\.\-\*åäöÅÄÖ\s\+,]{'.$min.','.$max.'}$/i', $str)) {
         //$this->error[$id] = 'Använd '.$min.' till '.$max.'st vanliga tecken.';
         $this->error[$id] = 'Use atleast '.$min.' characters';
         return false;
      } else return true;
   }
   // }}}
   // numeric {{{
   /**
    * Checks string for numeric characters
    * Return true if the string is valid.
    * Else return false and put a error mark in $error variable
    *
    * @param string id  identifier
    * @param int min    minimum amount of chars
    * @param int max    maximum amount of chars
    */
   public function numeric($id, $min, $max)
   {
      $str = $this->getStr($id);

      if (!preg_match('/^[\d\/\-\s\.]{'.$min.','.$max.'}$/', $str) ) {
         $this->error[$id] = textid('system/validate/use').' '.
            $min.' -> '.$max.' '.textid('system/validate/num_char').'.';

         return false;
      } else return true;
   }
   // }}}
   // number {{{
   /**
    * Checks string for number only
    * Return true if the string is valid.
    * Else return false and put a error mark in $error variable
    *
    * @param string id  identifier
    * @param int min    minimum number
    * @param int max    maximum number
    */
   public function number($id, $min, $max)
   {
      $str = $this->getStr($id);
      if(!preg_match('/^[\d]{'.$min.','.$max.'}$/', $str) ) {
         $this->error[$id] = 'Only use a number between '.$min.' to '.$max;
         return false;
      }
      else return true;
   }
   // }}}
   // length {{{
   /**
    * Check string for valid length
    * Return true if the string is valid.
    * Else return false and put a error mark in $error variable
    *
    * @param string id  identifier
    * @param int min    minimum amount of chars
    * @param int max    maximum amount of chars
    */
   public function length($id, $min = 0, $max = 65535)
   {
      $str = $this->getStr($id);

      if(strlen($str) > $max || strlen($str) < $min) {
         //echo 'im here ok! max: '.$max.' min: '.$min.' and id: '.$id;
         
         $this->error[$id] = textid('system/validate/use').' '.
            $min.' -> '.$max.' '.textid('system/validate/chars').'.';

         return false;
      } else return true;
   }
   // }}}
   // email {{{
   /**
    * Check string for valid email
    * Return true if the string is valid.
    * Else return false and put a error mark in $error variable
    *
    * @param id string  identifier
    * @param int min    if the field is required to be filled
    */
   public function email($id, $min = 1)
   {
      $str = $this->getStr($id);

      if(($min == 0) && strlen($str) < 1) {
         return true;
      }

      $pattern = "/^[\w\.\-åäöÅÄÖ]+@[\w\.\-äåö]+\.[a-z]{2,5}$/i";

      if (!preg_match($pattern, $str)) {
         //$this->error[$id] = 'Du måste skriva in en korrekt emailaddress';
         $this->error[$id] = 'You need a correct email (name@example.com)';
         return false;
      } else {
         return true;
      }
   }
   // }}}
   // word {{{
   /**
    * Check string to be sane (one word only)
    * Return true if the string is valid.
    * Else return false and put a error mark in $error variable
    *
    * @param string id  identifier
    * @param int min    minimum amount of chars
    * @param int max    if the field is required to be filled
    */
   public function word($id, $min = 1, $max = 40)
   {
      $str = $this->getStr($id);
      if(($min == 0) && strlen($str) < 1) return true;

      $pattern = '/^[\w\-_,\.åäöÅÄÖ]{'.$min.','.$max.'}$/i';

      if (!preg_match($pattern, $str)) {
         //$this->error[$id] = 'Använd endast a-Ö,-_0-9';
         $this->error[$id] = 'Use only a-Z 0-9 -, _ . and atleast '.$min.' cars.';
         return false;
      } else return true;
   }
   // }}}
   // zip {{{
   /**
    * Check for valid zip format
    * Return true if the string is valid.
    * Else return false and put a error mark in $error variable
    *
    * @param string  id  identifier
    * @param int     min minimum amount of chars
    */
   public function zip($id, $min = 1)
   {
      $str = $this->getStr($id);


      if (($min == 0) && (strlen($str) < 1)) {
         return true;
      }
      if (!preg_match("/^[\d]{3}[-\s][\d]{2}$/", $str)) {
         $this->error[$id] = 'Godkänt postnummer: 000-00 och 000 00';
         return false;
      } else {
         return true;
      }
   }
   // }}}
   // idnr {{{
   /**
    * Checks for id number xxxxxx-xxxx | xxxxxx xxxx
    * Return true if the string is valid.
    * Else return false and put a error mark in $error variable
    *
    * @param string id  identifier
    * @param int min    minimum amount of chars
    */
   public function idnr($id, $min = 1)
   {
      $str = $this->getStr($id);

      if (($min == 0) && (strlen($str) < 1)) {
         return true;
      }
      if (!preg_match("/^[\d]{6}[-\s][\d]{4}$/", $str)) {
         $this->error[$id] = 'Godkänt format: 000000-0000 och 000000 0000';
         return false;
      } else return true;
   }
   // }}}
   // dateFormat {{{
   /**
    * Checks for valid date Format xxxx-xx-xx
    * Return true if the string is valid.
    * Else return false and put a error mark in $error variable
    *
    * @param string id  identifier
    * @param int min    minimum amount of chars
    */
   public function dateFormat($id, $min=0)
   {
      $str = $this->getStr($id);
      if(($min == 0) && (strlen($str) < 1)) {
         return true;
      }
      if(!preg_match('/^\d{4}\-\d{2}\-\d{2}$/',$str)) {
         $this->error[$id] = 'Du måste använda formatet 0000-00-00';
         return false;
      } else return true;      
   }
   // }}}
   // url {{{
   /**
    * Check for valid url http://x.x
    *
    * @param string id  identifier
    * @param int min    minimum amount of chars
    * @param int max    if the field is required to be filled
    */
   public function url($id, $min = 0, $max = 40)
   {
      $str = $this->getStr($id);

      if($this->length($id, $min, $max) === false) {
         return false;
      }
      
      if (strlen($str) < 1 && $min < 1) return true;
      
      if(!preg_match('/^http\:\/\/.+$/',$str)) {
         $this->error[$id] = 'the url must be corret, ie http://www.google.com';

         return false;
      } else {
         return true;
      }
   }
   // }}}
   // sessid {{{
   /**
    * Check for valid session id
    *
    * @param string id  session_id()
    */
   public function sessid($id)
   {

      if(!preg_match('/^[\w\d]{32,32}$/i',$id)) {
         return false;
      } else {
         return true;
      }
   }
   // }}}
   // getErrors {{{
   /**
    * Return error array
    */
   public function getErrors()
   {
      if (! count($this->error)) {
         return false;
      }
      return $this->error;
   }
   // }}}
   // insErr($id,$str) {{{
   /**
    * Insert custom error
    */
   public function insErr($id,$str)
   {
      $this->error[$id] = $str;
      return false;
   }

   // }}}
   // validateAs {{{
   /**
    * Insert custom error
    *
    */
   public function validateAs($func,$field,$args = array())
   {
      $min = (isset($args['min'])) ? $args['min'] : 0;
      $max = (isset($args['max'])) ? $args['max'] : 65535;

      switch($func) {
         case 'char':
            return $this->char($field,$min,$max);
            break;

         case 'numeric':
            return $this->numeric($field,$min,$max);
            break;

         case 'number':
            return $this->number($field,$min,$max);
            break;

         case 'email':
            return $this->email($field,$min);
            break;

         case 'word':
            return $this->word($field,$min,$max);
            break;
            
         case 'zip':
            return $this->zip($field,$min);
            break;

         case 'idnr':
            return $this->idnr($field,$min);
            break;

         case 'dateFormat':
            return $this->dateFormat($field,$min);
            break;

         case 'url':
            return $this->url($field,$min,$max);
            break;

         case 'length':
            return $this->length($field,$min,$max);
            break;


         default:
            echo'validateing '.$field.' as length (default)';
            return $this->length($field,$min,$max);
            break;
      }
      return false;
   }

   // }}}
   // getStr($id) {{{
   protected function getStr($id)
   {
      $str = '';

      // handle multi dimensional arrays.
      if (strpos($id,'[') !== false) {
         $e = explode('[',$id);
         $var = $e[0];
         $key = substr($e[1],0,-1);
         $str = $this->str[$var][$key];
      } else {
         $str = $this->str[$id];
      }

      return $str;
   }
   // }}}
}
?>
