<?php
class Form_Plugin
{
	protected $v = array();
	protected $w = array();
	protected $pre = array();
   //__construct {{{
   public function __construct($p = array('values' => array(), 'errors' => array(),'prefix' => ''))
   {
      $this->v = isset($p['values']) ? $p['values'] : array();
      $this->e = isset($p['errors']) ? $p['errors'] : array();
      $this->pre = isset($p['prefix']) ? $p['prefix'] : array();
   }
   // }}}
   //text {{{
   public function text($name = '', $p = array())
   {
      $fieldname = $this->fixName($name);
      $value = isset($this->v[$name]) ? $this->v[$name] : '';
      $value = isset($p['force_value']) ? $p['force_value'] : $value;
      
      $id = !empty($p['id']) ? ' id="'.$p['id'].'" ' : '';
      $label = isset($p['label']) ? '<label>'.$p['label']."</label>\n" : '';

      $class = isset($p['class']) ? $p['class'] : '';
      if (isset($this->e[$name])) $class .= ' error';

      return $label.'<input type="text" class="'.$class.'" '.
         $id.
         'name="'.$fieldname.'" value="'.$value.'"/>'."\n";
   }
   // }}}
   //password {{{
   public function password($name = '', $p = array())
   {
      $fieldname = $this->fixName($name);
      $value = isset($this->v[$name]) ? $this->v[$name] : '';
      $label = isset($p['label']) ? '<label>'.$p['label']."</label>\n" : '';
      $class = isset($p['class']) ? $p['class'] : '';
      if (isset($this->e[$name])) $class .= ' error';

      return $label.'<input type="password" class="'.$class.'" '.
         'name="'.$fieldname.'" value="'.$value.'"/>';
   }
   // }}}
   //hidden {{{
   public function hidden($name = '',$p = array())
   {
      $fieldname = $this->fixName($name);
      $id = ( !empty($p['id'])) ? 'id="'.$p['id'].'" ' : '';

      $value = isset($this->v[$name]) ? $this->v[$name] : '';
      return '<input type="hidden" class="hidden" '.$id.
         'name="'.$fieldname.'" value="'.$value.'"/>';
   }
   // }}}
   //submit {{{
   public function submit($value = 'Submit',$p = array())
   {
      $style =  !empty($p['style']) ? ' style="'.$p['style'].'" ' : '';
      $id =  !empty($p['id']) ? ' id="'.$p['id'].'" ' : '';
      $class = !empty($p['class']) ? $p['class'] : 'button';
      return '<input type="submit" class="'.$class.'" value="'.$value.'"'.
         $style.$id.'/>';
   }
   // }}}
   //textarea {{{
   public function textarea($name = '',$p = array())
   {

      $fieldname = $this->fixName($name);
      $class = isset($p['class']) ? 'class="'.$p['class'].'" ' : '';
      $value = isset($this->v[$name]) ? $this->v[$name] : '';
      $cols = !empty($p['cols']) ? 'cols="'.$p['cols'].'" ' : '';
      $style = !empty($p['style']) ? 'style="'.$p['style']. '" ' : '';
      $id = !empty($p['id']) ? 'id="'.$p['id'].'" ' : '';
      $label = (!empty($p['label'])) ? '<label>'.$p['label'].'</label>' : '';

      return $label.'<textarea name="'.$fieldname.'" '.$cols.$style.$id.$class.'>'.$value.'</textarea>';
   }
   // }}}
   //checkbox {{{
   public function checkbox($name = '', $p = array())
   {

      $fieldname = $this->fixName($name);
      $chkd = (!empty($this->v[$name])) ? 'checked="checked" ' : '';
      $onclick = isset($p['onclick']) ? ' onclick="'.$p['onclick'].'" ' : '';

      $value = (!isset($p['value'])) ? '1' : $p['value'];

      return '<input type="checkbox" class="checkbox" id="'.$name.'" '.
         'name="'.$fieldname.'" value="'.$value.'" '.$chkd.$onclick.'/>';
   }
   // }}}
   //uploadFile {{{
   public function uploadFile($name = '',$p = array())
   {
      $fieldname = $this->fixName($name);

      return '<input type="file" class="file" '.
         'name="'.$fieldname.'"/>';
   }
   // }}}
   //select {{{
   public function select($name = '', $p = array())
   {
      if (empty($p['fields'])) return false;

      $fieldname = $this->fixName($name);
      $value = isset($this->v[$name]) ? $this->v[$name] : '';
      $value = isset($p['force_value']) ? $p['force_value'] : $value;
      $class = !empty($p['class']) ? ' class="'.$p['class'].'" ' : '';
      $id = !empty($p['id']) ? ' id="'.$p['id'].'" ' : '';
      $onchange = isset($p['onchange']) ? ' onchange="'.$p['onchange'].'" ' : '';
      $style = !empty($p['style']) ?  'style="'.$p['style'].'" ' : '';

      $fields = $p['fields'];

      $str = '<select name="'.$fieldname.'" '.$class.$onchange.$id.$style.'>';
      foreach($fields as $val => $text) {

         $selected = ($val == $value) ? 'selected="selected" ': '';
         $str .= '<option value="'.$val.'" '.$selected.'>'.
            $text.'</option>';
      }
      $str .= '</select>';

      return $str;
   }
   // }}}
   //selectNum {{{
   public function selectNum($name = '', $p = array())
   {
      $fieldname = $this->fixName($name);
      $value = isset($this->v[$name]) ? $this->v[$name] : '';
      $value = isset($p['force_value']) ? $p['force_value'] : $value;
      $class = !empty($p['class']) ? ' class="'.$p['class'].'" ' : '';
      $id = !empty($p['id']) ? ' id="'.$p['id'].'" ' : '';
      $onchange = isset($p['onchange']) ? ' onchange="'.$p['onchange'].'" ' : '';
      $style = !empty($p['style']) ?  'style="'.$p['style'].'" ' : '';


      $from = $p['from'];
      $to   = $p['to'];


      $str = '<select name="'.$fieldname.'" '.$class.$id.
         $onchange.$style.'>';

      for ($i = $from; $i <= $to;$i++) {
         $selected = ($i == $value) ? 'selected="selected" ': '';
         $str .= '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
      }

      $str .= '</select>';

      return $str;
   }
   // }}}
   //selectDate {{{
   public function selectDate($name = '', $p = array())
   {

      $value_y = 1970;
      $value_m = 1;
      $value_d = 1;

      $fieldname = $this->fixName($name);
      $class = isset($p['class']) ? 'class="'.$p['class'].'"' : '';
      $value = isset($this->v[$name]) ? $this->v[$name] : '';
      $str = $this->hidden($name,array('id' => $name));

      if (!empty($value)) {
         $exp = explode('/',date('Y/m/d',strtotime($value)));
         $value_y = $exp[0];
         $value_m = $exp[1];
         $value_d = $exp[2];
      }
      
      $str .= $this->select_num($name.'_y',array(
         'from' => '1994', 'to' => '2030','class' => 'year',
         'id' => $name.'_y', 'force_value' => $value_y,
         'onchange' => 'updateDate(\''.$name.'\');'
      ));

      $months = array(
         1 => textid('system/general/january'),
         2 => textid('system/general/february'),
         3 => textid('system/general/march'),
         4 => textid('system/general/april'),
         5 => textid('system/general/may'),
         6 => textid('system/general/june'),
         7 => textid('system/general/july'),
         8 => textid('system/general/august'),
         9 => textid('system/general/september'),
         10 => textid('system/general/october'),
         11 => textid('system/general/november'),
         12 => textid('system/general/december'),
      );
      //printArr($months);
      $str .= ' '.$this->select($name.'_m',array(
         'id' => $name.'_m',
         'force_value' => $value_m,
         'onchange' => 'updateDate(\''.$name.'\');',
         'fields' => $months,
      ));


      /*$str .= ' '.$this->select_num($name.'_m',array(
         'from' => '1', 'to' => '12','id' => $name.'_m',
         'class' => 'short',
         'force_value' => $value_m,
         'onchange' => 'updateDate(\''.$name.'\');'
      ));*/

      $str .= ' '.$this->select_num($name.'_d',array(
         'from' => '1', 'to' => '31','id' => $name.'_d',
         'class' => 'short',
         'force_value' => $value_d,
         'onchange' => 'updateDate(\''.$name.'\');'
      ));

      return $str;
   }
   // }}}
   //getErrors {{{
   public function getErrors()
   {
      return (is_array($this->e) && !empty($this->e)) ? $this->e : false;
   }
   // }}}
   //fixName {{{
   protected function fixName($name)
   {
      if (empty($this->pre)) {
         return $name;

      } else if (!empty($this->pre) && strpos($name,'[') === false) {
         return $this->pre.'['.$name.']';
      } else {
         $str = $this->pre.'[';
         if( preg_match('([^[]+)',$name,$matches)) {
            $str .= $matches[0].']'.substr($name,strlen($matches[0]));
         } else {
            echo "error with the form names!";
            return "";
         }
         return $str;

      }

   }
   // }}}
}
?>
