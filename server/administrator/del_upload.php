<?php
session_start();

ob_start();

include "../common.php";
include "../lib/func.php";

$db = new Database();

moderator_access();

$id = intval($_GET['id']);

$query = 'delete from screenshots where id='.$id;
$rs=$db->executeQuery($query);
unset($_SESSION['upload'][$id]);
header("Location: add_video.php?&search=".@$_GET['search']."&letter=".@$_GET['letter']."&page=".@$_GET['page']."#form");
?>