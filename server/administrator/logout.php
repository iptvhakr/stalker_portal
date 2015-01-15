<?php
session_start();

unset($_SESSION['login']);
unset($_SESSION['pass']);
header("Location: index.php");
?>