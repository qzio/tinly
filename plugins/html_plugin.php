<?php
// comment block {{{
/* vim: set expandtab tabstop=3 shiftwidth=3: */
// +--------------------------------------------------------+   
// | PHP Version 5.x                                        |
// +--------------------------------------------------------+
// | Filename: html_plugin.inc                              |
// +--------------------------------------------------------+
// | Copyright (c) 2008 Joel Hansson                        |
// +--------------------------------------------------------+
// | License: MIT                                           |
// +--------------------------------------------------------+
// | Author:   Joel Hansson <joel.hansson@gmail.com>              |
// +--------------------------------------------------------+
//
//  }}}

class Html_Plugin {
   protected $controller = '';
   public function __construct($p = array())
   {
      if (isset($p['controller'])) $this->controller = $p['controller'];
   }

   //line_break {{{
   public function line_break($height = '.3em')
   {
      return '<div style="height:'.$height.';">&nbsp;</div>';
   }
   // }}}
	// spaces {{{
   public function spaces($num = 1)
   {
      $str = '';
      for($i = 0; $i <= $num;$i++) {
         $str .= '&nbsp;';
      }
      return $str;
   }
	// }}}
	// link_to {{{
	public function link_to ($str, $controller = '', $action = '', $id = '')
	{
      $class ='';
		if (is_array($controller)) {
         $a = $controller;
			if (isset($a['url'])) {
				return '<a href="'.$a['url'].'">'.$str.'</a>';
			}
         $id = isset($a['id']) ? $a['id'] : '';
         $action = isset($a['action']) ? $a['action'] : '';
         $controller = isset($a['controller']) ? $a['controller'] : '';
         $class = isset($a['class']) ? 'class="'.$a['class'].'"' : '';

         // fallback to initial controller.
         $controller = (!empty($controller)) ? $controller : $this->controller;
		}

		$q_str = 'q='.$controller.'/'.$action.'/'.$id;
		//return '<a href="/?'.$q_str.'" '.$class.'>'.$str.'</a>';
      //
      $l_str = $controller.'/'.$action.'/'.$id;
		return '<a href="/'.$l_str.'" '.$class.'>'.$str.'</a>';
	}
	// }}}
   // script {{{
   public function script($script)
   {
      return '<script type="text/javascript" src="/public/javascripts/'.$script.'"></script>'."\n";
   }
   // }}}
   // p {{{
   public static function p ($desc = '',$a = array(),$backtrace = false)
   {
      if (!is_string($desc)) {
         $a = $desc;
         $desc = '';
      } else if( is_string($desc)) {
         $desc  .= ': ';
      } 
      if ($backtrace) {
         foreach($a as $num => $arr) {
            foreach($arr as $k => $v) {
               if ($k != 'file' && $k != 'line' && $k != 'function' && $k != 'class') {
                  unset($a[$num][$k]);
               }
            }
         }
      }
      if (defined('UA') && UA == 'curl') {
         return $desc.' '.print_r($a,true)."\n";
      }
      return $desc.' <pre style="text-align:left;font-size:13px;">'.print_r($a,true).'</pre>'."\n";
   }
   // }}}
   // h($str); returnes nl2br(htmlentities($str)) {{{
   public static function h ($str)
   {
      return nl2br(htmlentities($str,ENT_COMPAT,'UTF-8'));
   }// }}}
   // toggleBox($label,$content) {{{
   public static function toggleBox($label,$content)
   {
      $id = 'a'.rand(100,100);
      ob_start();
      ?>
         <script type="text/javascript">
         if (typeof(toggleBox) == 'undefined') {
            function toggleBox(id) {
               var elm = document.getElementById(id);
               if (elm.style.display == 'block') {
                  elm.style.display = 'none';
               } else {
                  elm.style.display = 'block';
               }
            }
         }
         </script>
         <a href="#" onclick="toggleBox('<?=$id?>');return false"> <?=$label?></a>
         <div id="<?=$id?>" style="display:none"><?=$content?></div>
      <?php
      $string = ob_get_contents();
      ob_flush();
      return $string;
   }// }}}
   

}
?>
