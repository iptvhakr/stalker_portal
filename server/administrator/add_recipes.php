<?php
session_start();

ob_start();

include "./common.php";

$error = '';

$db = new Database();

moderator_access();

if (@$_GET['del']){
    $query = "delete from recipes where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    header("Location: add_recipes.php");
}

if (!$error){
    
    if (@$_GET['save'] && !$error){
    
        if(@$_POST['name'] && @$_POST['recipe_cat_id_1'] && @$_POST['descr'] && @$_POST['ingredients']){
    
            $query = "insert into recipes (
                                        name,
                                        recipe_cat_id_1,
                                        recipe_cat_id_2,
                                        recipe_cat_id_3,
                                        recipe_cat_id_4,
                                        descr,
                                        ingredients
                                        ) 
                                values ('".@$_POST['name']."', 
                                        '".@$_POST['recipe_cat_id_1']."', 
                                        '".@$_POST['recipe_cat_id_2']."', 
                                        '".@$_POST['recipe_cat_id_3']."', 
                                        '".@$_POST['recipe_cat_id_4']."', 
                                        '".@$_POST['descr']."', 
                                        '".@$_POST['ingredients']."'
                                        )";
            //echo $query;
            $rs=$db->executeQuery($query);
            header("Location: add_recipes.php");
        }
        else{
            $error = 'Ошибка: необходимо заполнить все поля';
        }
    }
    
    if (@$_GET['update'] && !$error){
        
        if(intval(@$_GET['id'])>0 && @$_POST['name'] && @$_POST['recipe_cat_id_1'] && @$_POST['descr'] && @$_POST['ingredients']){
            
            $query = "update recipes 
                                set name='".$_POST['name']."', 
                                recipe_cat_id_1='".$_POST['recipe_cat_id_1']."', 
                                recipe_cat_id_2='".$_POST['recipe_cat_id_2']."', 
                                recipe_cat_id_3='".$_POST['recipe_cat_id_3']."', 
                                recipe_cat_id_4='".$_POST['recipe_cat_id_4']."', 
                                descr='".$_POST['descr']."', 
                                ingredients='".$_POST['ingredients']."'
                            where id=".intval(@$_GET['id']);
            $rs=$db->executeQuery($query);
            header("Location: add_recipes.php");
        }
        else{
            $error = 'Ошибка: необходимо заполнить все поля';
        }
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
</style>
<title>
Редактирование списка РЕЦЕПТОВ
</title>
</head>
<body onload="init_cats();">
<table align="center" width="640" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Редактирование списка РЕЦЕПТОВ&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< Назад</a>
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
<?

function get_cats(){
    $db = Database::getInstance();
    
    $query = "select * from recipe_cats order by title";
    $rs=$db->executeQuery($query);
    $option = '';
    $str = "var all_cats = [ ";
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        $str .= "{ id : ".$arr['id'].", title : '".$arr['title']."'},"; 
    }
    $str = substr($str, 0, strlen($str)-1);
    $str .= " ]";
    return $str;
}

function get_selected_cats(){
    $db = Database::getInstance();
    
    $genre_id = array();
    $str = '';
    if (@$_GET['id']){
        $id = $_GET['id'];
        $sql = "select * from recipes where id=".$id;
        $rs=$db->executeQuery($sql);
        //if ($rs->getRowCount() == 1){
            $cat_id[1] = $rs->getValueByName(0, 'recipe_cat_id_1');
            $cat_id[2] = $rs->getValueByName(0, 'recipe_cat_id_2');
            $cat_id[3] = $rs->getValueByName(0, 'recipe_cat_id_3');
            $cat_id[4] = $rs->getValueByName(0, 'recipe_cat_id_4');
        //}
    
    
        $cat_id[1] = $cat_id[1] ? $cat_id[1] : 0;
        $cat_id[2] = $cat_id[2] ? $cat_id[2] : 0;
        $cat_id[3] = $cat_id[3] ? $cat_id[3] : 0;
        $cat_id[4] = $cat_id[4] ? $cat_id[4] : 0;
        
        $k=0;
        
        for ($i = 1; $i <= 4; $i++){
            if ($cat_id[$i] > 0){
                $k ++;
            }
        }
        
        $str  = "var sel_cat_id_1 = ".$cat_id[1]."\n";
        $str .= "var sel_cat_id_2 = ".$cat_id[2]."\n";
        $str .= "var sel_cat_id_3 = ".$cat_id[3]."\n";
        $str .= "var sel_cat_id_4 = ".$cat_id[4]."\n";
        $str .= "var total_cats   = ".$k."\n";
    }
    return $str;
}

$query = "select * from recipes order by name";

$rs=$db->executeQuery($query);
echo "<center><table class='list' cellpadding='3' cellspacing='0'>";
echo "<tr>";
echo "<td class='list'><b>Название</b></td>";
echo "<td class='list'>&nbsp;</td>";
echo "</tr>";
while(@$rs->next()){
    
    $arr=$rs->getCurrentValuesAsHash();
    
    echo "<tr>";
    echo "<td class='list'>".$arr['name']."</td>";
    echo "<td class='list'><a href='?edit=1&id=".$arr['id']."#form'>edit</a>&nbsp;&nbsp;";
    echo "<a href='?del=1&id=".$arr['id']."'>del</a>&nbsp;&nbsp;";
    echo "</tr>";
}
echo "</table></center>";

if (@$_GET['edit']){
    $query = "select * from recipes where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        $name = $arr['name'];
        $ingredients = $arr['ingredients'];
        $descr = $arr['descr'];
    }
}
?>
<script>
<?
echo get_selected_cats();
echo "\n";
echo get_cats();
echo "\n";

?>

function save(){
    form_ = document.getElementById('form_')
    
    name = document.getElementById('name').value
    id = document.getElementById('id').value
    
    action = 'add_recipes.php?id='+id

    if(document.getElementById('action').value == 'edit'){
        action += '&update=1'
    }
    else{
        action += '&save=1'
    }
    
    form_.action = action
    form_.method = 'POST'
    form_.submit()
}

function init_cats(){
    if (typeof(total_cats) != 'undefined'){
        for (var i=1; i<=total_cats; i++){
            document.getElementById('cat_'+i).style.display = ''
            fill_cats(i)
        }
        if (i<=4){
            document.getElementById('cat_'+(i)).style.display = ''
            fill_cats(i)
        }
    }else{
        fill_cats(1)
    }
}

function cat_proc(num){
    if (num < 4){
        cat_id_obj = document.getElementById('cat_id_'+num)
        cat_obj = document.getElementById('cat_'+num)
        if (cat_id_obj.options[cat_id_obj.selectedIndex].value != '0'){
            fill_cats(num+1)
            document.getElementById('cat_'+(num+1)).style.display = ''
        }else{
            for(i = 1; i <= 4; i++){
                if (i > num){
                    document.getElementById('cat_id_'+i).options.length = 0;
                    document.getElementById('cat_'+i).style.display = 'none'
                }
            }
        }
    }
}

function fill_cats(num){
    cat_id_obj = document.getElementById('cat_id_'+num)
    cat_id_obj.options[0] = new Option('- - -', 0)
    selected = false
    for (var i=0; i<all_cats.length; i++){
        try{
            if (eval('sel_cat_id_'+num) == all_cats[i].id){
                selected = true
            }else{
                selected = false
            }
        }catch(e){}
        cat_id_obj.options[i+1] = new Option(all_cats[i].title, all_cats[i].id, selected)
    }
}

</script>
<br>
<table align="center" class='list'>
<tr>
    <td>
    &nbsp;
    </td>
</tr>
<tr>
    <td>
    <form id="form_" method="POST">
    <table align="center">
        <tr>
           <td align="right">
            Название: 
           </td>
           <td>
            <input type="text" name="name" size="50" id="name" value="<? echo @$name ?>">
            <input type="hidden" id="id" value="<? echo @$_GET['id'] ?>">
            <input type="hidden" id="action" value="<? if(@$_GET['edit']){echo "edit";} ?>">
           </td>
        </tr>
        
        <tr id="cat_1" style="background-color:#e0e0e0">
           <td align="right" valign="top">
            Категория 1: 
           </td>
           <td>
            <select name="recipe_cat_id_1" id="cat_id_1" onchange="cat_proc(1)">
            </select>
           </td>
        </tr> 
        <tr id="cat_2" style="display:none;background-color:#e0e0e0">
           <td align="right" valign="top">
            Категория 2: 
           </td>
           <td>
            <select name="recipe_cat_id_2" id="cat_id_2" onchange="cat_proc(2)">
            </select>
           </td>
        </tr>  
        <tr id="cat_3" style="display:none;background-color:#e0e0e0">
           <td align="right" valign="top">
            Категория 3: 
           </td>
           <td>
            <select name="recipe_cat_id_3" id="cat_id_3" onchange="cat_proc(3)">
            </select>
           </td>
        </tr> 
        <tr id="cat_4" style="display:none;background-color:#e0e0e0">
           <td align="right" valign="top">
            Категория 4: 
           </td>
           <td>
            <select name="recipe_cat_id_4" id="cat_id_4" onchange="cat_proc(4)">
            </select>
           </td>
        </tr>
        
        <tr>
           <td align="right">
            Ингредиенты: 
           </td>
           <td>
            <textarea id="ingredients" name="ingredients" rows="10" cols="38"><? echo @$ingredients ?></textarea>
           </td>
        </tr>
        <tr>
           <td align="right">
            Описание: 
           </td>
           <td>
            <textarea id="descr" name="descr" rows="10" cols="38"><? echo @$descr ?></textarea>
           </td>
        </tr>
        <tr>
           <td>
           </td>
           <td>
            <input type="button" value="Сохранить" onclick="save()">&nbsp;<input type="button" value="Новый" onclick="document.location='add_recipes.php'">
           </td>
        </tr>
    </table>
    </form>
    <a name="form"></a>
    </td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>