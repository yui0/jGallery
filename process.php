<?php
require 'config.php';

if (isset($_GET['id'])) {
	//echo $_GET["id"];
	//echo $_GET["f"];
	$pdo = new PDO('sqlite:'.$path.'list.db');
	$pdo->prepare('UPDATE list SET flag=? WHERE id=?')->execute([$_GET['f'], $_GET['id']]);
	//$pdo->query('UPDATE list SET flag='.$_GET['f'].' WHERE id='.$_GET['id']);
}
?>
