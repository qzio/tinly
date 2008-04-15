<?php
require_once 'config.php';
require_once 'lib/__autoload.php';
require_once 'lib/dbcon.php';
foreach($pdo->query('show tables') as $t) {
	$table = $t[0];
	$sql = 'drop table '.$table;
	$pdo->exec($sql);
	echo $sql."\n";
}
?>
