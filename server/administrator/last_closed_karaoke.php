<?php
session_start();

ob_start();

include "./common.php";
include "./lib/tasks.php";

use Stalker\Lib\Core\Mysql;

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

if (@$_GET['archive'] == 1 && @$_GET['id']){

    Admin::checkAccess(AdminAccess::ACCESS_CONTEXT_ACTION);

    $id = intval(@$_GET['id']);
    
    $year  = date("Y");
    $month = date("n");
    
    if ($month == 1){
        $month = 12;
        $year--;
    }else{
        $month--;
    }
    
    // video archive

    $archive_id = Mysql::getInstance()
        ->from('tasks_archive')
        ->where(array(
            'month' => $month,
            'year'  => $year
        ))
        ->get()
        ->first('id');
    
    if (empty($archive_id)){
        $archive_id = Mysql::getInstance()->insert('tasks_archive', array(
            'date'  => 'NOW()',
            'year'  => $year,
            'month' => $month
        ));
    }

    Mysql::getInstance()->update('moderator_tasks',
        array(
            'archived'      => $archive_id,
            'archived_time' => 'NOW()'
        ),
        array(
            'archived' => 0,
            'ended'    => 1,
            'to_usr'   => $id
        )
    );

    // karaoke archive

    $archive_id = Mysql::getInstance()
        ->from('karaoke_archive')
        ->where(array(
            'month' => $month,
            'year'  => $year
        ))
        ->get()
        ->first('id');
    
    if (empty($archive_id)){
        $archive_id = Mysql::getInstance()->insert('karaoke_archive', array(
            'date'  => 'NOW()',
            'year'  => $year,
            'month' => $month
        ));
    }

    Mysql::getInstance()->update('karaoke',
        array(
            'archived'      => $archive_id,
            'archived_time' => 'NOW()'
        ),
        array(
            'archived' => 0,
            'status'   => 1,
            'accessed' => 1
        )
    );

    header("Location: last_closed_tasks.php?id=".$id);
    exit();
}


if (isset($_GET['accessed']) && @$_GET['id']){

    Admin::checkAccess(AdminAccess::ACCESS_CONTEXT_ACTION);

    set_karaoke_accessed(@$_GET['id'], @$_GET['accessed']);
    $id = @$_GET['id'];
    if ($_GET['accessed'] == 1){
        chmod(KARAOKE_STORAGE_DIR.'/'.$id.'.mpg', 0444);
    }else{
        chmod(KARAOKE_STORAGE_DIR.'/'.$id.'.mpg', 0666);
    }
    header("Location: last_closed_karaoke.php?id=".@$_GET['uid']);
    exit;
}

if (isset($_GET['returned']) && @$_GET['id']){

    Admin::checkAccess(AdminAccess::ACCESS_CONTEXT_ACTION);

    set_karaoke_returned(@$_GET['id'], @$_GET['returned'],@$_GET['reason']);
    header("Location: last_closed_karaoke.php?id=".@$_GET['uid']);
    exit;
}

if (isset($_GET['done']) && @$_GET['id']){

    Admin::checkAccess(AdminAccess::ACCESS_CONTEXT_ACTION);

    set_karaoke_done(@$_GET['id'], @$_GET['done']);
    $id = @$_GET['id'];
    
    header("Location: last_closed_karaoke.php?id=".@$_GET['uid']);
    exit;
}


function set_karaoke_accessed($id, $val){

    if (!$id){
        return;
    }

    Mysql::getInstance()->update('karaoke', array('accessed' => $val, 'added' => 'NOW()'), array('id' => $id));
}

function set_karaoke_done($id, $val){

    if (!$id){
        return;
    }

    Mysql::getInstance()->update('karaoke', array('done' => $val, 'done_time' => 'NOW()'), array('id' => $id));
}

function set_karaoke_returned($id, $val, $txt){

    if (!$id){
        return;
    }

    Mysql::getInstance()->update('karaoke',
        array(
            'returned' => $val,
            'reason'   => $txt,
            'done'     => intval(!$val)
        ),
        array('id' => $id)
    );
}

function get_karaoke_accessed($id){
    $karaoke = Karaoke::getById($id);
    return $karaoke['accessed'];
}

function get_done_karaoke($id){
    $karaoke = Karaoke::getById($id);
    return $karaoke['done'];
}

function get_karaoke_accessed_color($id){
    if (get_karaoke_accessed($id)){
        $color = 'green';
        $accessed = 0;
        $txt = 'on';
    }else{
        $color = 'red';
        $accessed = 1;
        $txt = 'off';
    }
    $letter = @$_GET['letter'];
    $search = @$_GET['search'];
    if (Admin::isPageActionAllowed()){
        return "<a href='last_closed_karaoke.php?accessed=$accessed&id=$id&uid=".@$_GET['id']."&letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']."'><font color='$color'>$txt</font></a>";
    }else{
        return "<font color='$color'><b>$txt</b></font>";
    }
}

function get_done_karaoke_color($id){
    if(get_done_karaoke($id)){
        $color = 'green';
        $done = 0;
        $txt = _('done');
    }else{
        $color = 'red';
        $done = 1;
        $txt = _('not done');
    }
    $letter = @$_GET['letter'];
    $search = @$_GET['search'];
    if (Admin::isPageActionAllowed()){
        return "<a href='last_closed_karaoke.php?done=$done&id=$id&uid=".@$_GET['id']."&letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']."'><font color='$color'>$txt</font></a>";
    }else{
    	return "<font color='$color'><b>$txt</b></font>";
    }
}

function return_karaoke($id, $returned, $reason){
	if($returned == 1){
		$txt = _('returned');
		$color = 'red';
		$returned = 0;
	}else{
		$txt = _('return');
        $color = '#CBCB00';
        $returned = 1;
	}
	if (Admin::isPageActionAllowed()){
		//return "<a href='last_closed_karaoke.php?return=1&id=$id&uid=".@$_GET['id']."&letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']."'><font color='#CBCB00'>вернуть</font></a>";
		$str  = "<a href='#' ";
		if ($returned == 0){
			$str .= "title='$reason'";
		}
		$str .= " onclick='reason = prompt(\""._('Return reason').":\"); if(reason){document.location = \"last_closed_karaoke.php?returned=$returned&id=$id&uid=".@$_GET['id']."&reason=\"+reason} '><font color='$color'>$txt</font></a>";
		return $str;
	}else{
	   $str  = "<a href='#' ";
        if ($returned == 0){
            $str .= "title='$reason'";
        }
        $str .= " ><font color='$color'>$txt</font></a>";
        return $str;
	}
}

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style type="text/css">

body {
    font-family: Arial, Helvetica, sans-serif;
    font-weight: bold;
}
td {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 14px;
    text-decoration: none;
    color: #000000;
}
.list{
    border-width: 1px;
    border-style: solid;
    border-color: #E5E5E5;
}
a{
	color:#0000FF;
	font-weight: bold;
	text-decoration:none;
}
a:link,a:visited {
	color:#5588FF;
	font-weight: bold;
}
a:hover{
	color:#0000FF;
	font-weight: bold;
	text-decoration:underline;
}
a.msgs:hover, a.msgs:visited, a.msgs:link{
	font-family: Arial, Helvetica, sans-serif;
	font-size: 14px;
    color:#000000;
	font-weight: bold;
	text-decoration:none;
}
</style>
<title><?= _('Completed Karaoke tasks')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Completed Karaoke tasks')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="stat_moderators.php"><< <?= _('Back')?></a>
    </td>
</tr>
<tr>
    <td align="center">
    <font color="Red">
    <strong>
    <? echo $error?>
    </strong>
    </font>
    <br>
    <br>
    </td>
</tr>
<tr>
<td>

<table border="0" align="center" width="620">
<tr>
<td>
<font color="Gray">* <?= _('report generated by closed-month clips')?></font>
</td>
</tr>
</table>

</td>
</tr>
</table>
<?

$where = '';

if (Admin::isPageActionAllowed()){
    $uid = @$_GET['id'];
}else{
    $uid = @$_SESSION['uid'];
}
?>
    
    <table border="0" align="center" width="760">
    <tr>
    <td align="center">
    
    <table border="1" width="100%" cellspacing="0">
        <tr>
            <td>#</td>
            <td><?= _('Title')?></td>
            <td><?= _('Performer')?></td>
            <td><?= _('Turn on date')?></td>
            <td>&nbsp;</td>
        </tr>
        <?

        $karaoke = Mysql::getInstance()
            ->from('karaoke')
            ->where(array(
                'archived' => 0,
                'add_by'   => $uid
            ))
            ->orderby('name')
            ->get();

        $num = 0;

        while($arr_done = $karaoke->next()){

            $num++;
            
            echo "<tr>";
            echo "<td>$num</td>";
            echo "<td>".$arr_done['name']."</td>";
            echo "<td>".$arr_done['singer']."</td>";
            echo "<td>".$arr_done['added']."</td>";
            echo "<td>";
            echo get_karaoke_accessed_color($arr_done['id'])."&nbsp;&nbsp;";
            echo get_done_karaoke_color($arr_done['id'])."&nbsp;&nbsp;";
            echo return_karaoke($arr_done['id'], $arr_done['returned'], $arr_done['reason']);
            echo "</td>";
            echo "</tr>";
        }
        ?>
    </table>
    <br>
    <br>
    
    <br>
    <br>
    <table border="0" width="100%">
    </table>
    <td>
    </tr>
    </table>
