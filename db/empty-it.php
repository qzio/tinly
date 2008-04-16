<?php
require_once 'config.php';
require_once 'lib/__autoload.php';
if (!is_object($pdo)) {
	$pdo = pdo_wrap::getInstance(Config::$DB_DSN,Config::$DB_USER,CONFIG::$DB_PASSWORD);
}
foreach($pdo->query('show tables') as $t) {
	$table = $t[0];
	$sql = 'drop table '.$table;
	$pdo->exec($sql);
	echo $sql."\n";
}
echo "done droping tables\n";
?>
