<?php
function tv_chanel(){
    get_tv_chanel();
    tv_favorites();
    tv_channel_link();
    tv_genres();
    epg();
}


function get_tv_chanel(){
    $html='';
    global $done_test,$fail_test,$key,$url_test;
    $html.='</td></tr>';
     $json_string = file_get_contents ($url_test."/users/1557/settings", false, stream_context_create (array ('http'=>array ('method'=>'PUT','header'=>"Authorization: Bearer ".$key."\r\n".
        "Accept: application/json\r\n",'content'=>"parent_password=0000"))));
    $json_string = file_get_contents ($url_test."/users/1557/tv-channels", false, stream_context_create (array ('http'=>array ('method'=>'GET','header'=>"Authorization: Bearer ".$key."\r\n".
        "Accept: application/json\r\n"))));
    $html.="<tr><td colspan=2><h3>Ресурс USERS tv-channels: </h3></td></tr>";
    $html.="<tr><td><b>Статус:</b></td><td color='black'>";
    $obj = json_decode($json_string,true);
    $id_done=0;
    $name_done=0;
    $number_done=0;
    $url_done=0;
    $arhive_done=0;
    $censored_done=0;
    $favorite_done=0;
    if(strtoupper($obj['status'])=="OK"){
         if(empty($obj['results'][0]['id'])){
             $html.="<b style='color:red;'>Отсутствует в ответе.</b>";
         }   
            $id= array(97,123,17,121,172,33,122,128,275,107,166,226,225,224,223,222,227,175,195,176,23,35,127,125,196,116,152,83,178,149,151,153,154,52,231,184,146,101,155,115,156,47,150,19,27,29,38,177,228,208,209,210,230,234,237,236,235,238,239,14,18,37,251,252,253,254,255,80,15,242,241,31,62,67,68,159,160,81,84,61,58,73,72,165,164,69,36,51,233,162,167,91,90,93,76,53,54,55,56,57,50,74,49,77,168,170,180,229,189,190,193,194,245,246,247,248,249,138,173);
            $name= array("Интер","Новый канал","1 + 1","ICTV","Первый национальный","СТБ","ТРК Україна","ОРТ","Discovery World","Мега","M1","ОДТРК / Новая Одесса","А1","Ріак-інформ / Арт","Одеса Плюс","Репортер","Академия","Глас","АТВ 1","Град","Рада","Тонис","5й канал","КРТ","АТВ 2","Eurosport","Eurosport 2","Спорт один","ТРК Футбол","Наше кино","Совершенно секретно","НТВ-Мир","Парк развлечений","Euronews","Disney Channel","Nickelodeon (rus)","Детский мир","Discovery (тест)","Animal Planet (тест)","National Geographic ","AXN Sci Fi","Fashion TV","RTVi","2+2 - Кино","Enter-фільм","Enter Music","24 Новости","English Club","ОК (Одесса)","Интер+","RU Music","News One","Mezzo","Discovery Travel (тест)","Меню ТВ","Малятко ТВ (тест)","Спорт 2 (тест)","МАКСI ТВ (тест)","УТР (тест)","K1","K2","М-TV Украина","НТА","Культура","КДР ТРК","ЧП.INFO","Еко ТБ","ТВ Клуб (тест)","OTV","ТВi","CiTi (тест)","UBC (тест)","РБК (тест)","Союз (тест)","Bridge TV (тест)","Кинохит","Наше новое кино","Мульт2х2 (тест)","Первый авто канал (тест)","НТВ новости (тест)","ДТВ (тест)","Россия 24 (тест)","РТР Планета (тест)","Киноклуб (тест)","Премьера (тест)","A-ONE (тест)","М2 Эстрада","Первый канал (тест)","РТР-Россия (тест)","Теленяня","ТНТ","Mubasher (тест)","AL JAZEERA DOC (тест)","al Jazeera (тест)","Альджазира (тест)","EuroNews анг. (тест)","EuroNews фр. (тест)","EuroNews нем. (тест)","EuroNews ит. (тест)","EuroNews исп. (тест)","ББС (анг.) (тест)","СТ CCTV9 (тест)","КТО (ар.) (тест)","Шансон ТВ (тест)","Шант ТВ (ар.)","World Fashion","Россия 2","Nickelodeon (eng)","ACM","CTC","Куй ТВ","Luxe TV HD","Eurosport HD","Nat Geo HD","Nat Geo Wild HD","MTVN HD","Sport 1 HD Russia","НТН","ТЕТ");
            $number= array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,44,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,101,105,106,107,108,110,111,112,113,114,115,116,119,120,123,124,126,127,128,129,130,131,137,140,200,201,202,203,205,206,207,208,209,210,211,212,213,215,217,218,222,224,225,227,228,364,365,366,367,368,380,399);
            $url=array("","","rtp://239.1.1.60:1234","rtp://239.1.1.35:1234","","rtp://239.1.1.87:1234","rtp://239.1.1.7:1234","rtmp://85.195.93.10/ch/ch3147.stream","rtmp://85.195.93.10/ch/ch3172.stream","rtp://239.1.1.59:1234","rtp://239.1.1.36:1234","rtp://239.1.1.175:1234","rtp://239.1.1.174:1234","rtp://239.1.1.173:1234","rtp://239.1.1.172:1234","rtp://239.1.1.171:1234","rtp://239.1.1.170:1234","rtp://239.1.1.48:1234","udp://224.2.3.10:1234","rtp://239.1.1.49:1234","rtp://239.1.1.11:1234","rtp://239.1.1.40:1234","rtp://239.1.1.12:1234","rtp://239.1.1.10:1234","udp://224.2.3.11:1234","rtp://239.1.1.1:1234","rtp://239.1.1.26:1234","rtp://239.1.1.104:1234","rtp://239.1.1.9:1234","rtp://239.1.1.15:1234","rtp://239.1.1.17:1234","rtp://239.1.1.27:1234","rtp://239.1.1.16:1234","rtp://239.1.1.66:1234","rtp://239.1.1.107:1234","rtp://239.1.1.123:1234","rtp://239.1.1.14:1234","rtp://239.1.1.2:1234","rtp://239.1.1.5:1234","rtp://239.1.1.4:1234","rtp://239.1.1.32:1234","rtp://239.1.1.93:1234","rtp://239.1.1.13:1234","rtp://239.1.1.64:1234","rtp://239.1.1.81:1234","rtp://239.1.1.82:1234","rtp://239.1.1.39:1234","rtp://239.1.1.43:1234","rtp://239.1.1.84:1234","rtp://239.1.1.103:1234","rtp://239.1.1.105:1234","rtp://239.1.1.106:1234","rtp://239.1.1.3:1234","rtp://239.1.1.6:1234","rtp://239.1.1.45:1234","rtp://239.1.1.44:1234","rtp://239.1.1.28:1234","rtp://239.1.1.83:1234","rtp://239.1.1.109:1234","rtp://239.1.1.62:1234","rtp://239.1.1.63:1234","rtp://239.1.1.38:1234","rtp://239.1.1.8:1234","rtp://239.1.1.23:1234","rtp://239.1.1.25:1234","rtp://239.1.1.42:1234","rtp://239.1.1.90:1234","rtp://239.1.1.92:1234vtrack:601","rtp://239.1.1.61:1234","rtp://239.1.1.111:1234","rtp://239.1.1.108:1234","rtp://239.1.1.85:1234","rtp://239.1.1.72:1234","rtp://239.1.1.98:1234","rtp://239.1.1.97:1234","rtp://239.1.1.29:1234","rtp://239.1.1.30:1234","rtp://239.1.1.91:1234","rtp://239.1.1.22:1234","rtp://239.1.1.71:1234","rtp://239.1.1.70:1234","rtp://239.1.1.79:1234","rtp://239.1.1.75:1234","rtp://239.1.1.34:1234","rtp://239.1.1.33:1234","rtp://239.1.1.95:1234","rtp://239.1.1.37:1234","rtp://239.1.1.68:1234","rtp://239.1.1.19:1234","rtp://239.1.1.41:1234","rtp://239.1.1.46:1234","rtp://239.1.1.53:1234","rtp://239.1.1.54:1234","rtp://239.1.1.55:1234","rtp://239.1.1.80:1234","rtp://239.1.1.66:1234","rtp://239.1.1.66:1234","rtp://239.1.1.66:1234","rtp://239.1.1.66:1234","rtp://239.1.1.66:1234","rtp://239.1.1.67:1234","rtp://239.1.1.77:1234","rtp://239.1.1.69:1234","rtp://239.1.1.76:1234","rtp://239.1.1.78:1234","rtp://239.1.1.31:1234","rtp://239.1.1.52:1234","rtp://239.1.1.123:1234","rtp://239.1.1.58:1234","rtp://239.1.1.74:1234","rtp://239.1.1.89:1234","rtp://239.1.1.94:1234","rtp://239.1.1.113:1234","rtp://239.1.1.114:1234","rtp://239.1.1.115:1234","rtp://239.1.1.116:1234","rtp://239.1.1.117:1234","rtp://239.1.1.18:1234","rtp://239.1.1.47:1234");
            $archive=array(0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $censored=array(0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $favorite=array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            for($i=0;$i<count($obj['results']);$i++){
                if($id[$i]==$obj['results'][$i]['id']){
                    $id_done++;
                }
                if($name[$i]==$obj['results'][$i]['name']){
                    $name_done++;
                }
                if($number[$i]==$obj['results'][$i]['number']){
                    $number_done++;
                }
                if($url[$i]==$obj['results'][$i]['url']){
                    $url_done++;
                }
                if($archive[$i]==$obj['results'][$i]['archive']){
                    $archive_done++;
                }
                if($censored[$i]==$obj['results'][$i]['censored']){
                    $censored_done++;
                }
                if($favorite[$i]==$obj['results'][$i]['favorite']){
                    $favorite_done++;
                }
            }
            $all=count($obj['results']);
            $html.="'id' : ".$id_done."/".$all;
            if($id_done/$all!=1){
                $html.=" <b style='color:red;'>Не все</b>";
                $fail_test++;
            }
            else {
                $html.=" <b style='color:green;'>Ok</b>";
                $done_test++;
            }
            $html.="<br />'name' : ".$name_done."/".$all;
            if($name_done/$all!=1){
                $html.=" <b style='color:red;'>Не все</b>";
                 $fail_test++;
            }
            else {
                $html.=" <b style='color:green;'>Ok</b>";
                $done_test++;
            }
            $html.="<br />'number' : ".$number_done."/".$all;
            if($number_done/$all!=1){
                $html.=" <b style='color:red;'>Не все</b>";
                 $fail_test++;
            }
            else {
                $html.=" <b style='color:green;'>Ok</b>";
                $done_test++;
            }
            $html.="<br />'url' : ".$url_done."/".$all;
            if($url_done/$all!=1){
                $html.=" <b style='color:red;'>Не все</b>";
                 $fail_test++;
            }
            else {
                $html.=" <b style='color:green;'>Ok</b>";
                $done_test++;
            }
            $html.="<br />'archive' : ".$archive_done."/".$all;
            if($archive_done/$all!=1){
                $html.=" <b style='color:red;'>Не все</b>";
                 $fail_test++;
            }
            else {
                $html.=" <b style='color:green;'>Ok</b>";
                $done_test++;
            }
            $html.="<br />'censored' : ".$censored_done."/".$all;
            if($censored_done/$all!=1){
                $html.=" <b style='color:red;'>Не все</b>";
                 $fail_test++;
            }
            else {
                $html.=" <b style='color:green;'>Ok</b>";
                $done_test++;
            }
            $html.="<br />'favorite' : ".$favorite_done."/".$all;
            if($favorite_done/$all!=1){
                $html.=" <b style='color:red;'>Не все</b>";
                 $fail_test++;
            }
            else {
                $html.=" <b style='color:green;'>Ok</b>";
                $done_test++;
            }
            
    }
    else {
       $html.="<b style='color:red;'>Ошиька статуса</b>";
       $fail_test+=7;
    }
    echo $html;
}
function tv_favorites(){
    $html='';
    global $done_test,$fail_test,$key,$url_test;
    $html.='</td></tr>';
    $json_string = file_get_contents ($url_test."/users/1557/tv-favorites", false, stream_context_create (array ('http'=>array ('method'=>'POST','header'=>"Authorization: Bearer ".$key."\r\n".
        "Accept: application/json\r\n",'content'=>"ch_id=97"))));
    $html.="<tr><td colspan=2><h3>Ресурс USERS tv-favorites: </h3></td></tr>
        <tr><td colspan=2>Добавление канала.</td></tr>";
    $html.="<tr><td><b>Статус:</b></td><td color='black'>";
    $obj = json_decode($json_string,true);
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
    $json_string = file_get_contents ($url_test."/users/1557/tv-favorites", false, stream_context_create (array ('http'=>array ('method'=>'GET','header'=>"Authorization: Bearer ".$key."\r\n".
        "Accept: application/json\r\n"))));
    $html.="<tr><td colspan=2>Получение списка.</td></tr>";
    $obj = json_decode($json_string,true);
    $html.="<tr><td><b>Статус:</b></td><td color='black'>";
    if(!empty($obj['status'])){
        if(count($obj["results"])==1 && $obj["results"][0]==97){
            $html.=" <b style='color:green;'>Ok</b>";
            $done_test++;
        }
        else {
            $html.=" <b style='color:red;'>Не правильный список.</b>";
            $fail_test++;
        }
    }
    else {
        $html.=" <b style='color:red;'>Не правильный ответ.</b>";
        $fail_test++;
    }
    $json_string = file_get_contents ($url_test."/users/1557/tv-favorites", false, stream_context_create (array ('http'=>array ('method'=>'PUT','header'=>"Authorization: Bearer ".$key."\r\n".
        "Accept: application/json\r\n",'content'=>"ch_id=97,123,17"))));
    $html.="<tr><td colspan=2>Добавление списка.</td></tr>";
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
    $json_string = file_get_contents ($url_test."/users/1557/tv-channels?mark=favorite", false, stream_context_create (array ('http'=>array ('method'=>'GET','header'=>"Authorization: Bearer ".$key."\r\n".
        "Accept: application/json\r\n"))));
    $html.="<tr><td colspan=2>Получение списка с помощью tv-channels?mark=favorite.</td></tr>";
    $obj = json_decode($json_string,true);
    $html.="<tr><td><b>Статус:</b></td><td color='black'>";
    if(!empty($obj['status'])){
        if(count($obj["results"])==3 && $obj["results"][0]["id"]==97 && $obj["results"][1]["id"]==123 && $obj["results"][2]["id"]==17){
            $html.=" <b style='color:green;'>Ok</b>";
            $done_test++;
        }
        else {
            $html.=" <b style='color:red;'>Не правильный список.</b>";
            $fail_test++;
        }
    }
    else {
        $html.=" <b style='color:red;'>Не правильный ответ.</b>";
        $fail_test++;
    }
    $json_string = file_get_contents ($url_test."/users/1557/tv-favorites", false, stream_context_create (array ('http'=>array ('method'=>'GET','header'=>"Authorization: Bearer ".$key."\r\n".
        "Accept: application/json\r\n"))));
    $html.="<tr><td colspan=2>Получение списка.</td></tr>";
    $obj = json_decode($json_string,true);
    $html.="<tr><td><b>Статус:</b></td><td color='black'>";
    if(!empty($obj['status'])){
        if(count($obj["results"])==3 && $obj["results"][0]==97 && $obj["results"][1]==123 && $obj["results"][2]==17){
            $html.=" <b style='color:green;'>Ok</b>";
            $done_test++;
        }
        else {
            $html.=" <b style='color:red;'>Не правильный список.</b>";
            $fail_test++;
        }
    }
    else {
        $html.=" <b style='color:red;'>Не правильный ответ.</b>";
        $fail_test++;
    }
    $json_string = file_get_contents ($url_test."/users/1557/tv-favorites", false, stream_context_create (array ('http'=>array ('method'=>'PUT','header'=>"Authorization: Bearer ".$key."\r\n".
        "Accept: application/json\r\n",'content'=>"ch_id="))));
    $html.="<tr><td colspan=2>Очистка списка.</td></tr>";
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
    $json_string = file_get_contents ($url_test."/users/1557/tv-favorites", false, stream_context_create (array ('http'=>array ('method'=>'GET','header'=>"Authorization: Bearer ".$key."\r\n".
        "Accept: application/json\r\n"))));
    $html.="<tr><td colspan=2>Получение списка.</td></tr>";
    $obj = json_decode($json_string,true);
    $html.="<tr><td><b>Статус:</b></td><td color='black'>";
    if(!empty($obj['status'])){
        if(count($obj["results"])==0){
            $html.=" <b style='color:green;'>Ok</b>";
            $done_test++;
        }
        else {
            $html.=" <b style='color:red;'>Не правильный список.</b>";
            $fail_test++;
        }
    }
    else {
        $html.=" <b style='color:red;'>Не правильный ответ.</b>";
        $fail_test++;
    }
    echo $html;
}
function tv_genres(){
    $html='';
    global $done_test,$fail_test,$key,$url_test;
    $html.='</td></tr>';
    $json_string = file_get_contents ($url_test."/tv-genres", false, stream_context_create (array ('http'=>array ('method'=>'GET','header'=>"Authorization: Bearer ".$key."\r\n".
        "Accept: application/json\r\n"))));
    $html.="<tr><td colspan=2><h3>Ресурс TV-GENRES: </h3></td></tr><tr><td colspan=2>Получение списка жанров.</td></tr>";
    $obj = json_decode($json_string,true);
    $html.="<tr><td><b>Статус:</b></td><td color='black'>";
    $id=array('information','entertainments','childrens','cinema','science','sports','music','business','culture','for-adults');
    $title=array('информационный','развлечения','детское','кино','наука','спорт','музыка','бизнес','культура','для взрослых');
    $id_count=0;
    $title_count=0;
    $count=count($id);
    if(!empty($obj['status'])){
        for($i=0;$i<count($obj["results"]);$i++){
            if($obj["results"][$i]["id"]==$id[$i]){
                $id_count++;
            }
            if($obj["results"][$i]["title"]==$title[$i]){
                $title_count++;
            }            
        }
        $html.="'id' : ".$id_count."/".$count;
        if($id_count<$count){
            $html.=" <b style='color:red;'>Не все</b>";
            $fail_test++;
        }
        else {
            $html.=" <b style='color:green;'>Ok</b>";
            $done_test++;
        }
        $html.="<br />'title' : ".$title_count."/".$count;
        if($title_count<$count){
            $html.=" <b style='color:red;'>Не все</b>";
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
    $json_string = file_get_contents ($url_test."/tv-genres/cinema/tv-channels", false, stream_context_create (array ('http'=>array ('method'=>'GET','header'=>"Authorization: Bearer ".$key."\r\n".
        "Accept: application/json\r\n"))));
    $html.="<tr><td colspan=2>Получение списка каналов по жанру.</td></tr>";
    $obj = json_decode($json_string,true);
    $html.="<tr><td><b>Статус:</b></td><td color='black'>";
    if(count($obj["results"])==7 && $obj["results"][0]["id"]==149 && $obj["results"][6]["id"]==164){
        $html.=" <b style='color:green;'>Ok</b>";
        $done_test++;
    }
    else {
        $html.=" <b style='color:red;'>Не правильный ответ.</b>";
        $fail_test++;
    }
    echo $html;
}
function tv_channel_link(){
    $html='';
    global $done_test,$fail_test,$key,$url_test;
    $html.='</td></tr>';
    $json_string = file_get_contents ($url_test."/users/1557/tv-channels/123/link", false, stream_context_create (array ('http'=>array ('method'=>'GET','header'=>"Authorization: Bearer ".$key."\r\n".
        "Accept: application/json\r\n"))));
    $html.="<tr><td colspan=2><h3>Ресурс TV-CHANNELS LINK: </h3></td></tr><tr><td>Ответ:</td><td>$json_string</td></tr>";
    $obj = json_decode($json_string,true);
    $html.="<tr><td><b>Статус:</b></td><td color='black'>";
    if(!empty($obj["results"]) && strtoupper($obj["status"])=="OK"){
        $html.=" <b style='color:green;'>Ok</b>";
        $done_test++;
    }
    else {
        $html.=" <b style='color:red;'>Не правильный ответ.</b>";
        $fail_test++;
    }
    echo $html;
}

function epg(){
    $html='';
    global $done_test,$fail_test,$key,$url_test;
    $html.='</td></tr>';    
    $json_string = file_get_contents ($url_test."/tv-channels/17/epg?next=5", false, stream_context_create (array ('http'=>array ('method'=>'GET','header'=>"Authorization: Bearer ".$key."\r\n".
        "Accept: application/json\r\n"))));
    $html.="<tr><td colspan=2><h3>Ресурс EPG: </h3></td></tr>
        <tr><td colspan=2>Фиксированное кол-во передач</td></tr>";
        //<tr><td>Ответ:</td><td>$json_string</td></tr>";
    $obj = json_decode($json_string,true);
    $html.="<tr><td><b>Статус:</b></td><td color='black'>";
    $status=true;
    $id_archive=0;
    if(strtoupper($obj["status"])=="OK"){
        for($i=0;$i<count($obj["results"]);$i++){
            if(isset($obj["results"][$i]["id"]) || isset($obj["results"][$i]["name"])|| isset($obj["results"][$i]["start"]) || isset($obj["results"][$i]["end"]) || isset($obj["results"][$i]["in_archive"])){
                $status=true;
            }
            else {
                $status=false;
                break;
            }
        }
        if($status){
            $html.=" <b style='color:green;'>Ok</b>";
            $done_test++;
        }
        else {
            $html.=" <b style='color:red;'>Не правильный ответ.</b>";
            $fail_test++;
        }
    }
    else {
        $html.=" <b style='color:red;'>Не правильный ответ.</b>";
        $fail_test++;
    }
    $to=time();
    $now=$to-60*60*5;
    $json_string = file_get_contents ($url_test."/tv-channels/17/epg?from=$now&to=$to", false, stream_context_create (array ('http'=>array ('method'=>'GET','header'=>"Authorization: Bearer ".$key."\r\n".
        "Accept: application/json\r\n"))));
    $html.="<tr><td colspan=2>За промежуток времени.</td></tr>";
    $obj = json_decode($json_string,true);
    $html.="<tr><td><b>Статус:</b></td><td color='black'>";
    $status=true;
    if(strtoupper($obj["status"])=="OK"){
        for($i=0;$i<count($obj["results"]);$i++){
            if(isset($obj["results"][$i]["id"]) || isset($obj["results"][$i]["name"])|| isset($obj["results"][$i]["start"]) || isset($obj["results"][$i]["end"]) || isset($obj["results"][$i]["in_archive"])){
                if($obj["results"][$i]["in_archive"]){
                    $id_archive=$obj["results"][$i]["id"];
                }
            }
            else {
                $status=false;
                break;
            }
        }
        if($status){
            $html.=" <b style='color:green;'>Ok</b>";
            $done_test++;
        }
        else {
            $html.=" <b style='color:red;'>Не правильный ответ.</b>";
            $fail_test++;
        }
    }
    else {
        $html.=" <b style='color:red;'>Не правильный ответ.</b>";
        $fail_test++;
    }
    $html.="<tr><td colspan=2>Получение ссылки на архив передачи.</td></tr></tr><tr><td>Ответ:</td><td>$json_string</td></tr>";
    $html.="<tr><td><b>Статус:</b></td><td color='black'>";
    if($id_archive!=0){
        $json_string = file_get_contents ($url_test."/epg/$id_archive/link", false, stream_context_create (array ('http'=>array ('method'=>'GET','header'=>"Authorization: Bearer ".$key."\r\n".
        "Accept: application/json\r\n"))));
        $obj = json_decode($json_string,true);
        if(!empty($obj["results"]) && strtoupper($obj["status"])=="OK"){
            $html.=" <b style='color:green;'>Ok</b>";
            $done_test++;
        }
        else {
            $html.=" <b style='color:red;'>Не правильный ответ.</b>";
            $fail_test++;
        }
        
    }
    else {
       $html.=" <b style='color:red;'>Архив не доступен. Тест не проводился.</b>"; 
    }
    echo $html;
}
?>
