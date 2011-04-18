<?php
/**
 * Storage side engine
 *
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Storage
{
    private $media_ext_arr = array(
        'mpg',
        'mpeg',
        'avi',
        'ts',
        'mkv',
        'mp4',
        'mov'
    );
    
    private $media_ext_str = '';
    private $storage_name = '';
    
    public function __construct(){
        $this->media_ext_str = join('|', $this->media_ext_arr);
        $this->storage_name = ($_SERVER['SERVER_NAME'])? $_SERVER['SERVER_NAME'] : $_SERVER['SERVER_ADDR'];
    }
    /**
     * Create direstory for video
     *
     * @param string $name
     * @return boolean
     */
    public function createDir($name){
        if (!is_dir(VIDEO_STORAGE_DIR.$name)) {
            umask(0);
            if(!mkdir(VIDEO_STORAGE_DIR.$name, 0777)){
                throw new SoapFault('3', 'Could not create directory '.VIDEO_STORAGE_DIR.$name.' on '.$this->storage_name);
            }
        }
        return true;
    }
    
    /**
     * Check directory and return list of media files
     *
     * @param string $name
     * @param string $media_type
     * @return array
     */
    public function checkDir($name, $media_type){
        $result = array();
        
        $result['series'] = array();
        $result['series_file'] = array();
        $result['files']  = array();
        
        switch ($media_type){
            case 'vclub':
                $md5_sum = array();
                $md5_file = VIDEO_STORAGE_DIR.$name.'/'.$name.'.md5';
                
                if (is_file($md5_file)){
                    $md5_content = file($md5_file);
                    
                    foreach ($md5_content as $md5_record){
                        list($md5, $media_file) = @preg_split("/[\s\t]+/", $md5_record);
                        
                        if(strpos($media_file, "*") === 0){
                            $media_file = substr($media_file, 1, strlen($media_file));
                        }
                        
                        if (strpos($media_file, "/") !== false){
                            preg_match("/([^\/]*)\.(".$this->media_ext_str.")$/i", $media_file, $file_arr);
                            $media_file = $file_arr[0];
                        }
                        
                        $md5_sum[trim($media_file)] = trim($md5);
                    }
                }
                
                if(is_file('/tmp/'.$name.'_'.$this->storage_name.'.pid')){
                    $status = 'counting';
                }else{
                    $status = 'done';
                }
                
                if ($handle = @opendir(VIDEO_STORAGE_DIR.$name)) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && preg_match("/([\S\s]+)\.(".$this->media_ext_str.")$/i", $file)) { 
                            
                            key_exists($file, $md5_sum) ? $sum = $md5_sum[$file] : $sum='';
                            
                            $result['files'][] = array(
                                'name'   => $file,
                                'md5'    => $sum,
                                'status' => $status
                            );
                            
                            if (preg_match("/^([\d]+)\.(".$this->media_ext_str.")$/i", $file, $tmp_arr)){
                                $result['series'][] = $tmp_arr[1];
                                $result['series_file'][] = $file;
                            }
                        }
                    }
                    @closedir($handle);
                }else{
                    //throw new SoapFault('1', 'Could not open directory '.VIDEO_STORAGE_DIR.$name.' on '.$this->storage_name);
                }
                
                break;
            case 'karaoke':
                if (is_file(KARAOKE_STORAGE_DIR.$name)){
                    $result['files'][] = array('name' => $name, 'md5' => '');
                }else{
                    //throw new SoapFault('5', 'File '.KARAOKE_STORAGE_DIR.$name.' not exist on '.$this->storage_name);
                }
                break;
            default:
                throw new SoapFault('4', 'Undefined media type '.$media_type.' on '.$this->storage_name);
        }
        return $result;
    }
    
    /**
     * Create hard link $file in stb home directory
     *
     * @param string $mac
     * @param string $dir
     * @param string $file
     * @param integer $media_id
     * @param string $media_type
     * @return boolean
     */
    public function createLink($mac, $dir, $file, $media_id, $media_type = 'vclub'){
        $this->checkHomeDir($mac);
        switch ($media_type){
            case 'vclub':
                $path = VIDEO_STORAGE_DIR.$dir;
                break;
            case 'karaoke':
                $path = KARAOKE_STORAGE_DIR;
                break;
            default:
                throw new SoapFault('4', 'Undefined media type '.$media_type.' on '.$this->storage_name);
        }
        preg_match("/([\S\s]+)\.(".$this->media_ext_str.")$/i", $file, $arr);
        $ext = $arr[2];
        
        $from = $path.'/'.$file;
        $to = NFS_HOME_PATH.$mac.'/'.$media_id.'.'.$ext;
        
        if (!link($from, $to)){
            throw new SoapFault('2', 'Could not create link '.$from.' to '.$to.' on '.$this->storage_name);
        }
        
        if (!is_readable($to)){
            throw new SoapFault('6', 'File '.$to.' is not readable on '.$this->storage_name);
        }
        
        return true;
    }
    
    /**
     * Create stb home directory by MAC or clean it
     * 
     * @param string $mac
     * @return boolean
     */
    public function checkHomeDir($mac){
        $home = NFS_HOME_PATH.$mac;
        
        if (!is_dir($home)){
            umask(0);
            if(!mkdir($home, 0777)){
                throw new SoapFault('3', 'Could not create directory '.$home.' on '.$this->storage_name);
            }
        }else{
            if ($handle = @opendir($home)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && preg_match("/([\S\s]+)$/", $file)) { 
                        unlink($home.'/'.$file);
                    } 
                }
                @closedir($handle);
            }else{
                throw new SoapFault('1', 'Could not open directory '.$home.' on '.$this->storage_name);
            }
        }
        return true;
    }
    
    /**
     * Start counting MD5 SUM for media
     *
     * @param string $media_name
     */
    public function startMD5Sum($media_name){
        if (!is_dir(VIDEO_STORAGE_DIR.$media_name)){
            throw new SoapFault('9', 'Directory '.VIDEO_STORAGE_DIR.$media_name.' not exist on '.$this->storage_name);
        }
        
        if (!is_writable(VIDEO_STORAGE_DIR.$media_name)){
            throw new SoapFault('8', 'Directory '.VIDEO_STORAGE_DIR.$media_name.' is not writable on '.$this->storage_name);
        }
        
        if (!function_exists('exec')){
            throw new SoapFault('7', 'Function [exec] not exist on '.$this->storage_name);
        }
        
        $storage_dir = VIDEO_STORAGE_DIR;
        $launch_dir = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'],'/'));
        exec("$launch_dir/md5sumlauncher.sh $media_name $storage_dir $this->storage_name > /dev/null &");
        
    }
    
    /**
     * Stops process, which counting MD5 SUM for media
     *
     * @param string $media
     */
    public function stopMD5Sum($media){
        if (function_exists('exec')){
            if(is_file('/tmp/'.$media.'_'.$this->storage_name.'.pid')){
                
            }
        }else{
            throw new SoapFault('7', 'Function [exec] not exist on '.$this->storage_name);
        }
    }
}
?>