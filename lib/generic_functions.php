<?php
// Gather generic functions here.

// xml2array();parse SimpleXMLElement into assoc array {{{
function xml2array($simplexml)
{

	if (!is_object($simplexml)) return array();

	$arr = array();
	$lastk = '';
	foreach($simplexml as $k =>$v) {
		$test = array();
		$test = xml2array($v);
		$ck = (string)$k;
		if ($ck == $lastk) {
			$cur = count($arr);
			if ($cur <= 1) {
				$t = $arr[$lastk];
				$arr = array($t);
			}
			$ck = (count($arr));
		}
		if (empty($test)) {
			$arr[$ck] = urldecode((string)$v);
		} else {
			$arr[$ck] = $test;
		}
		$lastk = (string)$k;
	}
	return $arr;
}// }}}
// a(mixed) alias for numeric array(1,2,3) {{{
function a()
{
	return func_get_args();
}
// }}}
