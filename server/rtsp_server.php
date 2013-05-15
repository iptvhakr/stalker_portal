<?php
/**
 * RTSPServer Proxy Maiplayer<->VLC
 *
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

error_reporting (E_ALL);

set_time_limit (0);

putenv("TZ=Europe/Zaporozhye");

define("MAX_KEEP_ALIVE", 30); 
define("TIME_WAITING_RESP", 10); 
define("MEDIA_FROM_DB", 0);

class RTSPThread {

    var $videos = array(
        1 => '/media/1.mpg',
        2 => '/media/2.mpg'
    );
    
    var $buffer;
    var $lastActivity;
    var $socket;
    var $user_addr;
    var $rtp_addr;
    var $thread_id;
    var $exit_thread = 0;
    var $conn_refused;
    var $waiting_resp;
    var $send_time;
    var $db;
    var $length_time;
    var $spd_id;
    var $file_name;
    
    var $err_codes = array(
        400 => 'Bad request',
        403 => 'Forbidden',
        404 => 'Not found',
        405 => 'Method Not Allowed',
        451 => 'Parameter not understood',
        500 => 'Internal server error'
    );

    function RTSPThread($socket) {
        $this->socket = $socket;
        $this->buffer = '';
        $this->lastActivity = time();
        $this->thread_id = 0;
        $this->conn_refused = 0;
        $this->waiting_resp = 0;
        $this->send_time = 0;
        $this->db = '';
        $this->length_time = 0;
        $this->spd_id = 0;
        $this->file_name = '';
    }
    
    function initVLCManager(){
        $this->VLCManager = new VLCManager;
        $this->VLCManager->thread_id = $this->thread_id;
        $this->VLCManager->Start();
        if(!$this->VLCManager->vlc_connected){
            $this->sendError(500);
            $this->destroy();
        }
    }
    
    function getSpdInfo(){
        if (MEDIA_FROM_DB){
            $sql = 'select * from media where m_id = '.$this->spd_id;
            $rs=$this->db->executeQuery($sql);
            if($rs->getRowCount() == 1){
                $this->file_name = $rs->getValueByName(0, 'm_path');
                $this->length_time = $rs->getValueByName(0, 'm_time');
            }
            elseif($rs->getRowCount() == 0){
                $this->sendError(404);
                $this->destroy();
            }
            else{
                $this->sendError(500);
                $this->destroy();
            }
        }else{
            if ($this->videos[$this->spd_id]){
                $this->file_name = $this->videos[$this->spd_id];
                $this->length_time = 600;
            }else{
                $this->sendError(404);
                $this->destroy();
            }
        }
    }

    function Parse($block){

        $this->Error("RTSPServer <---------- Maiplayer\r\n".$block."\r\n\r\n");
        
        preg_match("/^(\S)+/", $block, $first_words);
        
        $splited_block = split("\r\n", $block);
        
        preg_match("/^(\S)+/", $block, $first_words);
        
        preg_match("/(\d)+/", $splited_block[1], $cseq);
        $this->cseq = $cseq[0];
        
        switch ($first_words[0]){
            
            case 'OPTIONS':
                {

                    /*if (!file_exists($this->file_name)){
                        $this->sendError(404);
                        break;
                    }*/
                    
                    $this->sendRespOptions();
                    
                    break;
                }
            case 'DESCRIBE':
                {
                    
                    preg_match("/rtsp:(\S)+/", $splited_block[0], $uri);
                    
                    $this->uri = $uri[0];
                    
                    $parsed_uri = parse_url($this->uri);
                    
                    preg_match( "/^(\d+)/", substr($parsed_uri['path'], 1), $mass);
                    
                    $this->spd_id = $mass[0];
                    
                    /*if (!file_exists($this->file_name)){
                        $this->sendError(404);
                        break;
                    }*/
                    
                    $this->getSpdInfo();
                    $this->sendRespDescribe();
                    break;
                }
            case 'SETUP':
                {
                    preg_match("/client_port=(\d)+/", $splited_block[2], $c_port);
                    
                    $this->client_port = $c_port[0];
                    
                    $this->sid = md5($this->user_addr.time());
                    
                    $this->sendRespSetup();
                    break;
                }
            case 'TEARDOWN':
                {
                    $this->sendRespTeardown();
                    $this->exit_thread = 1;
                    $this->destroy();
                    break;
                }
            case 'PLAY':
                {
                    preg_match("/(Range: npt=)+([\d]+)/", $block, $arr);
                    if (isset($arr[2]) && @$arr[2] >= 0){
                        $this->sendRespSeek($arr[2]);
                    }else{
                        $this->sendRespPlay();
                    }
                    break;
                }
            case 'PAUSE':
                {
                    $this->sendRespPause();
                    break;  
                }
            case 'RTSP/1.0':
                {
                    $this->waiting_resp = 0;
                    break;
                }
            default:
                {
                    $this->sendError(400);
                }
        }
        
    }
    
    function sendResp($resp){
        $this->Error("RTSPServer ----------> Maiplayer\r\n".$resp."\r\n\r\n");
        if(!@socket_write ($this->socket, $resp, strlen ($resp))){
              $this->conn_refused = 1;
        }
        $buf = '';
    }
    
    function sendError($num){
        $msg = "RTSP/1.0 ".$num." ".$this->err_codes[$num]."\r\n\r\n";
              //."CSeq: ".$this->cseq."\r\n\r\n";
              
        $this->sendResp($msg);
    }
    
    function sendOk($str = ''){
        $msg = "RTSP/1.0 200 OK\r\n"
              ."CSeq: ".$this->cseq."\r\n"
              ."Date: ".date("D, M d Y H:i:s", time())." GMT\r\n"
              .$str."\r\n";
        $this->sendResp($msg);
    }
    
    function sendRespOptions(){
        $msg = "var: OPTIONS, DESCRIBE, SETUP, TEARDOWN, PLAY, PAUSE\r\n";
        $this->sendOk($msg);
    }
    
    function sendRespDescribe(){
        
        $msg = "m=video 0 RTP/AVP 33\r\n"
              ."a=control:track1\r\n";
        
        $head = "Content-Base: $this->uri/\r\n"
               ."Content-Type: application/sdp\r\n"
               ."Content-Length: ".strlen($msg)."\r\n\r\n";
               
        $this->sendOk($head.$msg);
    }
    
    function sendRespSetup(){
        //$msg = "Transport: RTP/AVP;unicast;destination=$this->rtp_addr;$this->client_port;server_port=$this->rtp_port\r\n"
        $msg = "Transport: RTP/AVP;unicast;destination=$this->rtp_addr;$this->client_port;server_port=0\r\n"
              ."Session: ".$this->sid."\r\n";
        $this->sendOk($msg);
    }
    
    function sendRespPlay(){
        //$this->VLCManager->Play($this->file_name, $this->rtp_addr, $this->rtp_port, $this->sid);
        $this->VLCManager->Play($this->file_name, $this->rtp_addr, substr($this->client_port, 12), $this->sid);
        $this->sendOk();
    }
    
    function sendRespSeek($s_pos){
        $this->sendOk();
        
        $percent_pos = ceil(($s_pos/$this->length_time)*100);
        
        if ($percent_pos >= 100) $percent_pos = 99;
        
        $this->VLCManager->Seek($percent_pos);
    }
    
    function sendGetOption(){
        $this->cseq++;
        $msg = "GET_PARAMETER $this->uri RTSP/1.0\r\n"
              ."CSeq: $this->cseq\r\n"
              ."Session: $this->sid\r\n\r\n";
        $this->sendResp($msg);
        $this->waiting_resp = 1;
        $this->send_time = time();
    }
    
    function sendRespTeardown(){
        $this->sendOk();
        $this->VLCManager->End();
    }
    
    function endVLC(){
        $this->VLCManager->End();
    }
    
    function sendRespPause(){
        $this->VLCManager->Pause();
        $this->sendOk();
    }
    
    function Error($error_msg){
        //echo date("H:i:s ")."thread_id=".$this->thread_id." pid=".posix_getpid()."\r\n".$error_msg;
        echo date("H:i:s ", time())."thread_id=".$this->thread_id."\r\n".$error_msg;
        //$this->VLCManager->Error($error_msg);
    }
    
    function destroy() {
        @socket_close($this->socket);
        $this->exit_thread = 1;
    }
}

class RTSPServer {

    var $host;
    var $port;
    var $run;
    var $delay;
    var $sock;

    function RTSPServer($host, $port, $delay = 100000) {
        $this->host = $host;
        $this->port = $port;
        $this->delay  = $delay;
        $this->run  = true;
        
        if (!in_array("sockets", get_loaded_extensions())) {
            echo "--enable--sockets REQUIRED \n";
        }
        error_reporting (E_ALL);
        @set_time_limit (0);

        if (($this->sock = @socket_create (AF_INET, SOCK_STREAM, 0)) < 0) {
            echo "socket_create() failed: reason: " . socket_strerror ($this->sock) . "\n"; exit(1);
        }
        socket_set_option($this->sock, SOL_SOCKET, SO_REUSEADDR, 1); 
        if (($ret = @socket_bind ($this->sock, $this->host, $this->port)) < 0) {
            echo "socket_bind() failed: reason: " . socket_strerror ($ret) . "\n"; exit(1);
        }
        if (($ret = @socket_listen ($this->sock, 30)) < 0) {
            echo "socket_listen() failed: reason: " . socket_strerror ($ret) . "\n"; exit(1);
        }
        socket_set_nonblock($this->sock);
    }
    
    function run() {
        echo 1;
        $pool = array();
        while ($this->run) {
            usleep($this->delay);

            // Принимаем клиентов, которые стоят в очереди
            $currentTime = time();
            if (($msgsock = @socket_accept($this->sock))) {
                socket_set_nonblock($msgsock);
                socket_set_option($msgsock, SOL_SOCKET, SO_KEEPALIVE, 1);
                $pool[] =& new RTSPThread($msgsock);

            }

            // Обслуживаем клиентов
            foreach ($pool as $key => $client) {
                    
                if(!@$pool[$key]->VLCManager->vlc_connected){
                    
                    $pool[$key]->thread_id = $key;
                    $pool[$key]->initVLCManager();
                    
                    socket_getpeername($pool[$key]->socket, $ipaddress);
                    
                    $pool[$key]->user_addr = $ipaddress;
                    $pool[$key]->rtp_addr = $ipaddress;
                    
                    $pool[$key]->db = new Database();
                }

                if ($tmp = @socket_read($pool[$key]->socket, 1024)) {
                    $pool[$key]->buffer .= $tmp;
                    $pool[$key]->lastActivity = $currentTime;
                }

                if (preg_match("/\r\n\r\n/", $pool[$key]->buffer)){
                    $pool[$key]->Parse($pool[$key]->buffer);
                    $pool[$key]->buffer = '';
                }
                
                if(!$pool[$key]->waiting_resp && (($currentTime - $pool[$key]->lastActivity) > MAX_KEEP_ALIVE)){
                    $pool[$key]->sendGetOption();
                }
                
                if ($pool[$key]->waiting_resp && (($currentTime - $pool[$key]->send_time) > TIME_WAITING_RESP)){
                    $pool[$key]->endVLC();
                    $pool[$key]->destroy();
                    unset($pool[$key]);
                }
                
                if (@isset($pool[$key]) && @$pool[$key]->exit_thread){
                    unset($pool[$key]);
                }
            }
        }
        echo "Server shutdown \r\n";
    }
} 

class VLCManager
{
    //var $vlc_addr = '192.168.1.15';
    var $vlc_addr = VLC_ADDR;
    var $vlc_port = 4212;
    var $res_create;
    var $res_connect;
    var $rtp_name;
    var $rtp_addr;
    var $rtp_port;
    var $file;
    var $log_id;
    var $fl_play = 0;
    var $vlc_connected = 0;
    var $thread_id = 0;
    
    function Start(){
        
        $this->res_create = @socket_create (AF_INET, SOCK_STREAM, 0);
        if (!$this->res_create) {
            $this->Error("VLCManager socket_create() failed (line ".__LINE__."): reason: " . socket_strerror ($socket) . "\r\n");
        }
        
        $this->res_connect = @socket_connect ($this->res_create, $this->vlc_addr, $this->vlc_port);
        
        if (!$this->res_connect) {
            $this->Error("VLCManager socket_connect() failed (line ".__LINE__."): reason: (".$this->res_connect.") " . socket_strerror($this->res_connect) . "\r\n");
            $this->vlc_connected=0;
        }
        else{
            $this->vlc_connected=1;
            $this->Send("admin\r\n");
        }
        
    }
    
    function Send($msg){
        
        if ($this->res_connect){
            socket_write ($this->res_create, $msg, strlen ($msg));
            $this->Error("RTSPServer ----------> VLC\r\n".$msg."\r\n\r\n");
        }
    }
    
    function Play($file, $rtp_addr, $rtp_port, $rtp_name){
        $this->rtp_name = $rtp_name;
        $this->rtp_addr = $rtp_addr;
        $this->rtp_port = $rtp_port;
        //$this->file = $file;
        $this->file = str_replace("\\", "\\\\", $file);;
        
        $vlc_cmd = "new $this->rtp_name broadcast enabled\r\n"
                  ."setup $this->rtp_name input \"$this->file\"\r\n"
                  ."setup $this->rtp_name output "
                  //."setup $this->rtp_name loop"
                  ."#"
                  //."transcode{vcodec=mp2v,fps=25,vb=1024,scale=1,acodec=mpga,ab=192,channels=2}:"
                  ."standard{mux=ts,access=rtp,dst=$this->rtp_addr:$this->rtp_port,sap,name=\"$this->rtp_name\"}\r\n"
                  ."control $this->rtp_name play\r\n";
        
        if($this->fl_play){
            $this->Pause();
        }
        else {
            $this->Send($vlc_cmd);
            $this->fl_play = 1;
        }
    }
    
    function Pause(){
        
        $vlc_cmd = "control $this->rtp_name pause\r\n";
        
        $this->Send($vlc_cmd);
    }
    
    function Seek($s_pos){
        $vlc_cmd = "control $this->rtp_name seek ".$s_pos."\r\n";
        $this->Send($vlc_cmd);
        $this->Pause();
    }
    
    function Stop(){
        
        $vlc_cmd = "control ".$this->rtp_name." stop\r\n";
        
        $this->Send($vlc_cmd);
    }
    
    function Del(){
        
        $vlc_cmd = "del ".$this->rtp_name."\r\n";
        
        $this->Send($vlc_cmd);
    }
    
    function Quit(){
        
        $vlc_cmd = "exit\r\n";
        
        $this->Send($vlc_cmd);
    }
    
    function Close(){
        socket_close ($this->res_create);
    }
    
    function End(){
        /*$vlc_cmd = "control $this->rtp_name stop\r\n"
                  ."del $this->rtp_name\r\n"
                  ."exit\r\n";*/
        $vlc_cmd = "del $this->rtp_name\r\n"
                  ."exit\r\n";
        //$vlc_cmd = "del $this->rtp_name\r\n";
        
        $this->Send($vlc_cmd);
    }
    
    function Error($error_msg){
        //echo date("H:i:s ")."thread_id=".$this->thread_id." pid=".posix_getpid()."\r\n".$error_msg;
        echo date("H:i:s ", time())."thread_id=".$this->thread_id."\r\n".$error_msg;
        //$fp = fopen ("log_".$this->log_id.".txt", "a");
        //fwrite($fp, date("H:i:s ").$error_msg);
        //fclose($fp);
    }
}

/* Указать IP адрес VLC*/
define("VLC_ADDR", "192.168.1.15");




$a = new RTSPServer("0.0.0.0", "8888");
$a->run(); 

?>