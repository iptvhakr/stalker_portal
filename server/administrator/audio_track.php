<?php
session_start();

ob_start();

include "./common.php";

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

if (@$_GET['del'] && !empty($_GET['id'])){

    Admin::checkAccess(AdminAccess::ACCESS_DELETE);

    Mysql::getInstance()->delete('audio_compositions', array('id' => intval($_GET['id'])));
    header("Location: audio_track.php");
    exit;
}