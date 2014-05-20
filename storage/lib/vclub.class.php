<?php

class Vclub extends Storage
{
    public function __construct(){
        parent::__construct();
    }

    /**
     * Check directory and return list of media files
     *
     * @param string $name
     * @return array
     */
    public function checkMedia($name){
        $result = array();

        $result['series'] = array();
        $result['series_file'] = array();
        $result['files']  = array();

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

            $file_list = scandir(VIDEO_STORAGE_DIR.$name);

            $subtitles = array_filter($file_list, function($file){
                return in_array(substr($file, strrpos($file, '.') + 1), array('srt', 'sub', 'ass'));
            });

            while (false !== ($file = readdir($handle))) {

                if ($file != "." && $file != ".." && preg_match("/([\S\s]+)\.(".$this->media_ext_str.")$/i", $file)) {

                    array_key_exists($file, $md5_sum) ? $sum = $md5_sum[$file] : $sum='';

                    $info = array(
                        'name'   => $file,
                        'md5'    => $sum,
                        'status' => $status
                    );

                    if (preg_match("/^([\d]+)\.(".$this->media_ext_str.")$/i", $file, $tmp_arr)){
                        $result['series'][] = $tmp_arr[1];
                        $result['series_file'][] = $file;
                    }

                    $movie_base = substr($file, 0, strrpos($file, '.'));

                    $movie_subtitles = array_filter($subtitles, function($subtitle_file) use ($movie_base, $result){
                        if (empty($result['series'])){
                            return strpos($subtitle_file, $movie_base) === 0;
                        }else{
                            return substr($subtitle_file, 0, strrpos($subtitle_file, '.')) == $movie_base;
                        }
                    });

                    if (!empty($movie_subtitles)){
                        $info['subtitles'] = array_values($movie_subtitles);
                    }

                    $result['files'][] = $info;
                }
            }
            @closedir($handle);
        }else{
            //throw new IOException('Could not open directory '.VIDEO_STORAGE_DIR.$name.' on '.$this->storage_name);
        }

        return $result;
    }

    /**
     * Create hard link $file in stb home directory
     *
     * @param string $media_file
     * @param int $media_id
     * @param string $proto
     * @return boolean
     */
    public function createLink($media_file, $media_id, $proto = ''){

        $this->user->checkHome();

        preg_match("/([\S\s]+)\.(".$this->media_ext_str.")$/i", $media_file, $arr);

        $ext = $arr[2];

        $from = VIDEO_STORAGE_DIR.$media_file;

        $path = dirname($from);
        $file = basename($from);

        $movie_base = substr($file, 0, strrpos($file, '.'));

        $file_list = scandir($path);

        if ($file_list === false){
            throw new IOException('Could not scan dir'.$path.' on '.$this->storage_name);
        }

        $subtitles = array_filter($file_list, function($file){
            return in_array(substr($file, strrpos($file, '.') + 1), array('srt', 'sub', 'ass'));
        });

        $movie_subtitles = array_filter($subtitles, function($subtitle_file) use ($movie_base){
            return strpos($subtitle_file, $movie_base) === 0;
        });

        if (!empty($movie_subtitles)){
            foreach ($movie_subtitles as $subtitle){

                $from_sub = $path.'/'.$subtitle;
                $to_sub   = NFS_HOME_PATH.$this->user->getMac().'/'.$subtitle;

                if ($proto == 'http'){
                    $link_result = @symlink($from_sub, $to_sub);
                }else{
                    $link_result = @link($from_sub, $to_sub);
                }

                if (!$link_result){
                    throw new IOException('Could not create link '.$from_sub.' to '.$to_sub.' on '.$this->storage_name);
                }
            }
        }

        $to = NFS_HOME_PATH.$this->user->getMac().'/'.$media_id.'.'.$ext;

        if ($proto == 'http'){
            $link_result = @symlink($from, $to);
        }else{
            $link_result = @link($from, $to);
        }

        if (!$link_result){
            throw new IOException('Could not create link '.$from.' to '.$to.' on '.$this->storage_name);
        }

        if (!is_readable($to)){
            throw new IOException('File '.$to.' is not readable on '.$this->storage_name);
        }

        return true;
    }

    /**
     * Create directory for video
     *
     * @param string $name
     * @return boolean
     * @throws Exception
     */
    public function createDir($name){
        if (!is_dir(VIDEO_STORAGE_DIR.$name)) {
            umask(0);
            if(!mkdir(VIDEO_STORAGE_DIR.$name, 0777)){
                throw new IOException('Could not create directory '.VIDEO_STORAGE_DIR.$name.' on '.$this->storage_name);
            }
        }
        return true;
    }

    /**
     * Start counting MD5 SUM for media
     *
     * @param string $media_name
     * @throws Exception
     */
    public function startMD5Sum($media_name){
        
        if (!is_dir(VIDEO_STORAGE_DIR.$media_name)){
            throw new IOException('Directory '.VIDEO_STORAGE_DIR.$media_name.' not exist on '.$this->storage_name);
        }

        if (!is_writable(VIDEO_STORAGE_DIR.$media_name)){
            throw new IOException('Directory '.VIDEO_STORAGE_DIR.$media_name.' is not writable on '.$this->storage_name);
        }

        if (!function_exists('exec')){
            throw new BadFunctionCallException('Function [exec] not exist on '.$this->storage_name);
        }

        $storage_dir = VIDEO_STORAGE_DIR;
        $launch_dir = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'],'/'));
        exec("$launch_dir/md5sumlauncher.sh $media_name $storage_dir $this->storage_name > /dev/null &");
    }
}

?>