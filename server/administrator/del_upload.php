<?php
session_start();

ob_start();

include "../conf_serv.php";
include "../getid3/getid3.php";
include "../lib/func.php";

$db = new Database(DB_NAME);

moderator_access();

$id = intval($_GET['id']);

$query = 'delete from screenshots where id='.$id;
$rs=$db->executeQuery($query);
unset($_SESSION['upload'][$id]);
header("Location: add_video.php?&search=".@$_GET['search']."&letter=".@$_GET['letter']."&page=".@$_GET['page']."#form");
?>