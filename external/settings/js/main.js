var spos=0;
var _GET = {};
var close=false;

(function(){
   var get = new String(window.location);
   var x = get.indexOf('?');
   if (x!=-1){
       var l = get.length;
       get = get.substr(x+1, l-x);
       l = get.split('&');
       x = 0;
       for(var i in l){
           if (l.hasOwnProperty(i)){
               get = l[i].split('=');
               _GET[get[0]] = get[1];
               x++;
           }
       }
   }
})();

function init_m()
{
    punkti=[[t('Parental'),"ico_lock","ico_lock_act","ico_l_lang","g_pass.html"],
    [t('Localization'),"ico_lang","ico_lang_act","ico_l_lang","g_local.html"],
    [t('Software update'),"ico_reload","ico_reload_act","ico_l_reload","g_update.html"],
    [t('Network info'),"ico_netinfo","ico_netinfo_act","ico_l_netinfo","g_netw.html"],
    [t('Video'),"ico_video","ico_video_act","ico_l_video","g_video.html"],
    [t('Audio'),"ico_audio","ico_audio_act","ico_l_audio","g_audio.html"],    
    [t('Network'),"ico_net","ico_net_act","ico_l_net","g_nets.html"],
    [t('Advanced settings'),"ico_advset","ico_advset_act","ico_l_advset","g_adv.html"],
    [t('Servers'),"ico_server","ico_server_act","ico_l_server","g_serv.html"],    
    [t('Device info'),"ico_sysinfo","ico_sysinfo_act","ico_l_sysinfo","g_dev.html"],
    [t('Reload portal'),"ico_exit","ico_exit_act","ico_l_exit",2],
    [t('Go to the inner portal'),"ico_switch","ico_switch_act","ico_l_switch",1],
    [t('Reboot device'),"ico_reboot","ico_reboot_act","ico_l_reboot",3]];
    //punkti[-1]=[t('default'),"ico_empty","ico_empty_act",""];
    kol=punkti.length;
    nextMenu('glavnaya.html');
    document.body.style.display="block";
}

function nextMenu(urlP)
{
    switch(urlP){
        case 0:
            switch(punkti[vid-1][4])
            {case 1:conf(t("Do you want to go to the inner portal?"),'','stb.LoadURL("file:///home/web/services.html");stbWebWindow.close();');break;
            case 2:conf(t("Do you want to restart portal?"),'','stb.LoadURL("file:///home/web/index.html");stbWebWindow.close();');break;
            case 3:conf(t("Device is going to reboot. Are you sure?"),'','stb.ExecAction("reboot")');break;
            default:cont.style.visibility='hidden';cont.src=punkti[vid-1][4];document.getElementById("zagolovok").innerHTML=punkti[vid-1][0];document.getElementById("ico").innerHTML="<img src='style/"+put+"/"+punkti[vid-1][3]+".png' />";break;}break;
        case "glavnaya.html":cont.style.visibility='hidden';cont.src=urlP;document.getElementById("zagolovok").innerHTML=t("Settings");document.getElementById("ico").innerHTML="<img src='style/"+put+"/ico_l_set.png' />";break;
        default:cont.style.visibility='hidden';cont.src=urlP;document.getElementById("zagolovok").innerHTML=punkti[vid-1][0];document.getElementById("ico").innerHTML="<img src='style/"+put+"/"+punkti[vid-1][3]+".png' />";break;
    }
}

function onLoad()
{
    stb.EnableVKButton(true);
    switch(screen.height)
    {
      case 480:w=623;h=430;a=45;b=5;put=576;break;
      case 576:w=623;h=430;a=45;b=40;put=576;break;
      case 720:w=1142;h=584;a=60;b=50;put=720;break;
      case 1080:w=1142;h=584;a=380;b=230;put=720;break;
    }
    document.cookie = "mac=" + escape(parent.stb.GetDeviceMacAddress()) + '; path=/;';    
    load({"type":"stb","action":"get_settings_profile"},function(profile){prof=profile;});
    var fileref = document.createElement("link");
    fileref.setAttribute("rel", "stylesheet");
    fileref.setAttribute("type", "text/css");
    fileref.setAttribute("href", 'style/glav_' + put + '.css');    
    document.getElementsByTagName("head")[0].appendChild(fileref);
    window.resizeTo(w,h);
    window.moveTo(a,b);
    cont=document.getElementById("cont");
    cont.width=w;
    cont.height=h;
    langv();
}

function erMes(erm,el){
    close=false;
    elem=el;ind=1;maxin=2;
    var d=document.getElementById('confirm');d.style.display="block";
    document.getElementById('b1').focus();
    d.innerHTML='<div id="modal_ico" class="ico_alert"></div>'+erm+'<div id="modal_btn"><input id="b1" type="button" value="'+t('Ok')+'" onClick="erMbeck()" /></div>';
    document.getElementById('b1').focus();
}

function erMbeck(){
    if(close){stbWebWindow.close();}
    else {if(spos!=0)document.getElementById('confirmb').style.display="none";
    else document.getElementById('confirm').style.display="none";
    if(elem!='')cont.contentWindow.document.getElementById(elem).focus();
    else cont.focus();}
}

function conf(erm,el,ef,back){
    if(back)close=true;else close=false;
    elem=el;ind=1;maxin=2;
    func=ef;spos=0;
    var d=document.getElementById('confirm');d.style.display="block";
    document.getElementById('b1').focus();
    d.innerHTML='<div id="modal_ico" class="ico_issue"></div>'+erm+'<div id="modal_btn"><input id="b2" type="button" value="'+t('Cancel')+'" onClick="erMbeck()" /><input id="b1" type="button" value="'+t('Ok')+'" onClick="confOk()" /></div>';
    document.getElementById('b2').focus();
}

function confb(erm,el,ef,back){
    if(back)close=true;else close=false;
    elem=el;ind=1;maxin=2;
    func=ef;spos=1;
    var d=document.getElementById('confirmb');d.style.display="block";
    document.getElementById('b1b').focus();
    d.innerHTML='<div id="modal_ico" class="ico_issue"></div>'+erm+'<div id="modal_btn"><input id="b2b" type="button" value="'+t('Cancel')+'" onClick="erMbeck()" /><input id="b1b" type="button" value="'+t('Ok')+'" onClick="confOk()" /></div>';
    document.getElementById('b2b').focus();
}

function confOk(){
    eval(func);
    if(document.getElementById('confirmb').style.display=="none")document.getElementById('confirm').style.display="none";
    else document.getElementById('confirmb').style.display="none";
}

function pressKey(e)
{
ec = e.keyCode;
switch(ec)
    {
    case 9:e.preventDefault();break;
    case 40:perehod(1);break;
    case 38:perehod(-1);break;
    case 37:perehod(-1);break;
    case 39:perehod(1);break;
    }
}

function perehod(a)
{
    ind+=a;
    if(ind<1)ind=1;else if(ind>maxin)ind=maxin;
    if(spos!=0)document.getElementById('b'+ind+'b').focus();
    else document.getElementById('b'+ind).focus();
}

function load(params, callback){
   JsHttpRequest.query(
       'GET '+_GET.ajax_loader,

       params,

       function(result, errors){
        // errors - содержит ошибки сервера и debug сообщения
        gSTB.Debug(errors);

           callback(result);
       },

       true
   );
}