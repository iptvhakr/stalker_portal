<?php
namespace Lib;
use Imagick;

class Save extends \Lib\Base {
    private $_filename = null;
    private $_ext = null;
    private $_source = null;
    private $_target = null;

    private $resolutions = array(
        '320' => array('height' => 96, 'width' => 96),
        '240' => array('height' => 72, 'width' => 72),
        '160' => array('height' => 48, 'width' => 48),
        '120' => array('height' => 36, 'width' => 36)
    );

    private function _img_resize($src, $dest, $width, $height) {
    if (!file_exists($src) || empty($width) || empty($height)) { return false; };

        try{
            $icon = new \Imagick($src);
        }catch(ImagickException $e){
            $this->_error[] = $e->getMessage();
            return false;
        }

        $size = getimagesize($src);

        if ($size === false) { return false; };

        $x_ratio = $width / $size[0];
        $y_ratio = $height / $size[1];

        $ratio       = min($x_ratio, $y_ratio);
        $use_x_ratio = ($x_ratio == $ratio);

        $new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
        $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);

        if (!$icon->resizeImage($new_width, $new_height, Imagick::FILTER_LANCZOS, 1)){
            return false;
        }

        if (!$icon->writeImage($dest)){
            return false;
        }

        $icon->destroy();

        chmod($dest, 0666);

        return true;
    }

    public function save() {
        move_uploaded_file($this->_source, $this->_target);
        @unlink($this->_source);
        return true;
    }

    public function handleUpload($uploadDirectory, $new_name = '') {
        if (!is_writable($uploadDirectory)){
            $this->_error = 'Server error. Write in a directory: ' . $uploadDirectory . ' is impossible!';

            return false;
        }

        if (empty($_FILES)) {
            $this->_error = 'Server error. Something wrong with uploaded files!';

            return false;
        }
         while (list($files, $data) = each($_FILES)) {
            $this->_source = $_FILES[$files]['tmp_name'];
            $this->_ext = end(explode('.', strtolower($_FILES[$files]['name'])));

            $this->_filename = (empty($new_name)? $_FILES[$files]['name']: $new_name . '.' . $this->_ext);

            $this->_target = "$uploadDirectory/original/";

            if (!is_dir($this->_target)) {
                if (!@mkdir($this->_target, 0755)) {
                    $this->_error = "Server error. Directory for '$uploadDirectory/original/' original size dosn't exists" ;
                    return FALSE;
                }
            }
            
            $this->_target .= $this->_filename;
            
            if ($this->save()) {
                foreach ($this->resolutions as $resolution => $dimension) {
                    if ( (strtolower($this->_ext) == "gif") or (strtolower($this->_ext) == "png") or (strtolower($this->_ext) == "jpg") or (strtolower($this->_ext) == "jpeg") ) {
                        if (!is_dir("$uploadDirectory/$resolution/")) {
                            if(!@mkdir("$uploadDirectory/$resolution/", 0755)){
                                $this->_error = "Server error. Directory for '$uploadDirectory/$resolution/' size dosn't exists" ;
                                continue;
                            }
                        }
                        $this->_img_resize($this->_target, "$uploadDirectory/$resolution/" . $this->_filename, $dimension["width"], $dimension["height"]);
                    };
                }

                return true;
            } else {
                $this->_error = 'It is impossible to save the file.' . 'Cancelled, server error';

                return false;
            }
        }
        
        return empty($this->_error);
    }
    
    public function setThumbWidth($width) {
        $this->pre_width = $width;
    }
    public function setThumbHeight($height) {
        $this->pre_height = $height;
    }
    
    public function getFileName(){
        return $this->_filename;
    }
    
    public function removeFile($path, $name) {
        $folders = array_keys($this->resolutions);
        $folders[] = "original";
        foreach ($folders as $folder) {
            $full_path = "$path/$folder";
            if (is_dir($full_path)) {
                foreach (glob("$full_path/$name") as $filename) {
                    if (!@unlink($filename)){
                        $this->_error[] = "Error delete file $filename";
                    }
                }
            }
        }
    }
    
    public function renameFile($path, $old_name, $new_name) {
        $folders = array_keys($this->resolutions);
        $folders[] = "original";
        foreach ($folders as $folder) {
            $full_path = "$path/$folder";
            if (is_dir($full_path) && is_writable("$full_path/$old_name")) {
                if (!rename("$full_path/$old_name", "$full_path/$new_name")) {
                    $this->_error[] = "Error rename file $old_name";
                }
            } else {
                $this->_error[] = "Error rename file $old_name, isn't writable or missing dir";
            }
        }
    }
}