<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<?php
    include_once 'tv_chanels.php';
    include_once 'video.php';
    $done_test=0;
    $fail_test=0;
    $url_test="http://v2.api.sandbox.infomir.com.ua";
    $url_test_auth="http://auth.sandbox.infomir.com.ua/token";
    echo "<h1>Testing</h1><hr />";
    $html='<table border=1px>';
    echo $html;
    $rkey=login();
    $key=refresh_login();
    users_login();
    tv_chanel();
    video();
    en();
    $html='</table><hr />';
    $html.='Удачных тестов: '.$done_test.', провалено тестов: '.$fail_test;
    echo $html;
    
function refresh_login(){
        $html='';
        global $done_test,$fail_test,$rkey,$url_test_auth;
        $data_url="grant_type=refresh_token&refresh_token=$rkey";
        try{
            $json_string = file_get_contents ($url_test_auth, false, stream_context_create (array ('http'=>array ('method'=>'POST','content'=>$data_url))));
            $obj = json_decode($json_string);
            $html.="<tr><td colspan=2><h3>Восстановление ключа: </h3></td></tr><tr><td>Ответ:</h3></td><td>".$json_string."</td></tr>";
            $html.="<tr><td><b>Статус:</b></td><td color='black'>";
            $html.="'token_type' : ";
            if(empty($obj->token_type)){
                $html.="<b style='color:red;'>Отсутствует в ответе.</b>";
                $fail_test++;
            }
            else{
                if($obj->token_type=="bearer"){
                    $html.="<b style='color:green;'>Ok</b>";
                    $done_test++;
                }
                else {
                    $html.="<b style='color:red;'>Неправильный ответ.</b>";
                    $fail_test++;
                }
            }
            $html.="<br />'access_token' : ";
            if(empty($obj->access_token)){
                $html.="<b style='color:red;'>Отсутствует в ответе.</b>";
                $fail_test++;
            }
            else {
                $dd=strlen($obj->access_token);
                if($dd==37){
                    $html.="<b style='color:green;'>Ok</b>";
                    $done_test++;
                }
                else {
                    $html.="<b style='color:red;'>Неверная длина.</b>";
                    $fail_test++;
                }
            }
            $html.="<br />'refresh_token' : ";
            if(empty($obj->refresh_token)){
                $html.="<b style='color:red;'>Отсутствует в ответе.</b>";
                $fail_test++;
            }
            else {
                $dd=strlen($obj->refresh_token);
                if($dd==32){
                    $html.="<b style='color:green;'>Ok</b>";
                    $rkey=$obj->refresh_token;
                    $done_test++;
                }
                else {
                    $html.="<b style='color:red;'>Неверная длина.</b>";
                    $fail_test++;
                }
            }
            $html.="<br />'user_id' : ";
            if(empty($obj->user_id)){
                $html.="<b style='color:red;'>Отсутствует в ответе.</b>";
                $fail_test++;
            }
            else {
                if($obj->user_id==1557){
                    $html.="<b style='color:green;'>Ok</b>";
                    $done_test++;
                }
                else {
                    $html.="<b style='color:red;'>Неправильный ответ.</b>";
                    $fail_test++;
                }
            }
            $html.="<br />'expires_in' : ";
            if(empty($obj->expires_in)){
                $html.="<b style='color:red;'>Отсутствует в ответе.</b>";
                $fail_test++;
            }
            else {
                if($obj->expires_in==86400){
                    $html.="<b style='color:green;'>Ok</b>";
                    $done_test++;
                }
                else {
                    $html.="<b style='color:red;'>Неправильный ответ.</b>";
                    $fail_test++;
                }
            }
            echo $html;
            return $obj->access_token;
        }
        catch (Exception $e){
            $html.="<b style='color:red;'>Ошибка : ".$e->getMessage()."</b>";
            $html.='</td></tr>';
            echo $html;
        }
        
}

function login(){
        $html='';
        global $done_test,$fail_test,$url_test_auth;
        $data_url="grant_type=password&username=1557&password=12345";
        try{
            $json_string = file_get_contents ($url_test_auth, false, stream_context_create (array ('http'=>array ('method'=>'POST','header'=>"Content-Type: application/x-www-form-urlencoded \r\n",'content'=>$data_url))));
            $obj = json_decode($json_string);
            $html.="<tr><td colspan=2><h3>Авторизация: </h3></td></tr><tr><td>Ответ:</h3></td><td>".$json_string."</td></tr>";
            $html.="<tr><td><b>Статус:</b></td><td color='black'>";
            $html.="'token_type' : ";
            if(empty($obj->token_type)){
                $html.="<b style='color:red;'>Отсутствует в ответе.</b>";
                $fail_test++;
            }
            else{
                if($obj->token_type=="bearer"){
                    $html.="<b style='color:green;'>Ok</b>";
                    $done_test++;
                }
                else {
                    $html.="<b style='color:red;'>Неправильный ответ.</b>";
                    $fail_test++;
                }
            }
            $html.="<br />'access_token' : ";
            if(empty($obj->access_token)){
                $html.="<b style='color:red;'>Отсутствует в ответе.</b>";
                $fail_test++;
            }
            else {
                $dd=strlen($obj->access_token);
                if($dd==37){
                    $html.="<b style='color:green;'>Ok</b>";
                    $done_test++;
                }
                else {
                    $html.="<b style='color:red;'>Неверная длина.</b>";
                    $fail_test++;
                }
            }
            $html.="<br />'refresh_token' : ";
            if(empty($obj->refresh_token)){
                $html.="<b style='color:red;'>Отсутствует в ответе.</b>";
                $fail_test++;
            }
            else {
                $dd=strlen($obj->refresh_token);
                if($dd==32){
                    $html.="<b style='color:green;'>Ok</b>";
                    $done_test++;
                }
                else {
                    $html.="<b style='color:red;'>Неверная длина.</b>";
                    $fail_test++;
                }
            }
            $html.="<br />'user_id' : ";
            if(empty($obj->user_id)){
                $html.="<b style='color:red;'>Отсутствует в ответе.</b>";
                $fail_test++;
            }
            else {
                if($obj->user_id==1557){
                    $html.="<b style='color:green;'>Ok</b>";
                    $done_test++;
                }
                else {
                    $html.="<b style='color:red;'>Неправильный ответ.</b>";
                    $fail_test++;
                }
            }
            $html.="<br />'expires_in' : ";
            if(empty($obj->expires_in)){
                $html.="<b style='color:red;'>Отсутствует в ответе.</b>";
                $fail_test++;
            }
            else {
                if($obj->expires_in==86400){
                    $html.="<b style='color:green;'>Ok</b>";
                    $done_test++;
                }
                else {
                    $html.="<b style='color:red;'>Неправильный ответ.</b>";
                    $fail_test++;
                }
            }
            $data_url="grant_type=password&username=1557&password=1234511111111";
            $json_string = file_get_contents ($url_test_auth, false, stream_context_create (array ('http'=>array ('method'=>'POST','content'=>$data_url))));
            $error = json_decode($json_string);
            $html.="<br />'error' : ";
            if(empty($error->error)){
                $html.="<b style='color:red;'>Отсутствует в ответе.</b>";
                $fail_test++;
            }
            else {
                if($error->error=="invalid_client"){
                    $html.="<b style='color:green;'>Ok</b>";
                    $done_test++;
                }
                else {
                    $html.="<b style='color:red;'>Неправильный ответ.</b>";
                    $fail_test++;
                }
            }
            echo $html;
            return $obj->refresh_token;
        }
        catch (Exception $e){
            $html.="<b style='color:red;'>Ошибка : ".$e->getMessage()."</b>";
            $html.='</td></tr>';
            echo $html;
        }
        
}

function users_login(){
    $html='';
    global $done_test,$fail_test,$key,$url_test;
    $json_string = file_get_contents ($url_test."/users/1557/settings", false, stream_context_create (array ('http'=>array ('method'=>'GET','header'=>"Authorization: Bearer ".$key."\r\n".
        "Accept: application/json\r\n"))));
    $html.="<tr><td colspan=2><h3>Ресурс USERS settings: </h3></td></tr>
        <tr><td colspan=2>Получение настроек</td></tr>    
        <tr><td>Ответ:</h3></td><td>".$json_string."</td></tr>";
    $html.="<tr><td><b>Статус:</b></td><td color='black'>";
    $obj = json_decode($json_string);
    if(strtoupper($obj->status)=="OK"){
        $html.="'parent_password' : ";
        if(empty($obj->results->parent_password)){
            $html.="<b style='color:red;'>Отсутствует в ответе.</b>";
        }
        else {
            if($obj->results->parent_password=="0000"){
                $html.="<b style='color:green;'>Ok</b>";
                $done_test++;
            }
            else {
                $html.="<b style='color:red;'>Неправильный ответ.</b>";
                $fail_test++;
            }
        }
    }
    else {
       $html.="<b style='color:red;'>Ошиька статуса</b>";
       $fail_test++;
    }
    $json_string = file_get_contents ($url_test."/users/1557/settings", false, stream_context_create (array ('http'=>array ('method'=>'PUT','header'=>"Authorization: Bearer ".$key."\r\n".
        "Accept: application/json\r\n",'content'=>"parent_password=1111"))));
    $html.="<tr><td colspan=2>Установка настроек.</td></tr>
        <tr><td>Ответ:</h3></td><td>".$json_string."</td></tr>";
    $obj = json_decode($json_string,true);
    $html.="<tr><td><b>Статус:</b></td><td color='black'>";
    if(!empty($obj['status'])){
        if($obj["results"]){
            $html.=" <b style='color:green;'>Ok</b>";
            $done_test++;
        }
        else {
            $html.=" <b style='color:red;'>Не удалось добавить.</b>";
            $fail_test++;
        }
    }
    else {
        $html.=" <b style='color:red;'>Не правильный ответ.</b>";
        $fail_test++;
    }
    $json_string = file_get_contents ($url_test."/users/1557/settings", false, stream_context_create (array ('http'=>array ('method'=>'GET','header'=>"Authorization: Bearer ".$key."\r\n".
        "Accept: application/json\r\n"))));
    $html.="<tr><td colspan=2>Получение настроек</td></tr><tr><td>Ответ:</h3></td><td>".$json_string."</td></tr>";
    $html.="<tr><td><b>Статус:</b></td><td color='black'>";
    $obj = json_decode($json_string);
    if(strtoupper($obj->status)=="OK"){
        $html.="'parent_password' : ";
        if(empty($obj->results->parent_password)){
            $html.="<b style='color:red;'>Отсутствует в ответе.</b>";
        }
        else {
            if($obj->results->parent_password=="1111"){
                $html.="<b style='color:green;'>Ok</b>";
                $done_test++;
            }
            else {
                $html.="<b style='color:red;'>Неправильный ответ.</b>";
                $fail_test++;
            }
        }
    }
    else {
       $html.="<b style='color:red;'>Ошиька статуса</b>";
       $fail_test++;
    }
    echo $html;
}

function en(){
       $html='';
    global $done_test,$fail_test,$key,$url_test;
    $html.='</td></tr>';    
    $html.="<tr><td colspan=2><h3>Проверка локализации, английский. (VIDEO-GENRES): </h3></td></tr>";
    $json_string = file_get_contents ($url_test."/video-genres", false, stream_context_create (array ('http'=>array ('method'=>'GET','header'=>"Authorization: Bearer ".$key."\r\n".
        "Accept: application/json\r\n"."Accept-Language: en-US\r\n"))));
    $obj = json_decode($json_string,true);
    $html.="<tr><td>Ответ:</td><td color='black'>";
    $title=array('action','adventure','aerobics','art','biography','catastrophe','children\'s','comedy','cookery','criminal','dancing','detective','drama','erotica','fantasy','fiction','fishing','foreign','health','historical','history','horror','house/country','humourist','hunting','melodrama','military','musical','mysticism','nature','ours','science','series','show','sketch-show','sport','teach','technique','thriller','travels','western','yoga');
    $title_count=0;
    $count=count($title);
    if(!empty($obj['status'])){
        for($i=0;$i<count($obj["results"]);$i++){
            if($i!=0)$html.=",";
            $html.="'".$obj["results"][$i]["title"]."'";
            if($obj["results"][$i]["title"]==$title[$i]){
                $title_count++;
            }
        }
        $html.="</td></tr><tr><td><b>Статус:</b></td><td color='black'>";
        if($title_count<$count){
            $html.=" <b style='color:red;'>Не верно.</b>";
            $fail_test++;
        }
        else {
            $html.=" <b style='color:green;'>Ok</b>";
            $done_test++;
        }
    }
    else {
        $html.=" <b style='color:red;'>Не правильный ответ.</b>";
        $fail_test++;
    } 
    echo $html;
}
?>
</body>
</html>
