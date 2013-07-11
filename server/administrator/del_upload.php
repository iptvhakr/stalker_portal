<?php
session_start();

ob_start();

include "./common.php";

moderator_access();

$id = intval($_GET['id']);

Mysql::getInstance()->delete('screenshots', array('id' => $id));

unset($_SESSION['upload'][$id]);
header("Location: add_video.php?&search=".@$_GET['search']."&letter=".@$_GET['letter']."&page=".@$_GET['page']."#form");
?>