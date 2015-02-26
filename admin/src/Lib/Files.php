<?php
namespace Lib;

class Files extends \Lib\Base {
    private $_files = array();
    private $_dirs = array();
    private $_path = null;
    private $_totalsize = 0;
    private $_pre_img = null;
    private $_ico = null;

    // ICO MIME TYPE
    private $_MIME = array(
        array("name" => "img", "ico" => "image.png", "ext" => array("jpg", "jpeg", "gif", "png", "bmp")),
        array("name" => "doc", "ico" => "msword.png", "ext" => array("doc", "docx", "rtf", "oft")),
        array("name" => "pdf", "ico" => "pdf.png", "ext" =>  array("pdf", "djvu")),
        array("name" => "txt", "ico" => "text.png", "ext" =>  array("txt")),
        array("name" => "flv", "ico" => "flash.png", "ext" =>  array("flv")),
        array("name" => "exe", "ico" => "executable.png", "ext" =>  array("exe", "com", "bat")),
        array("name" => "xls", "ico" => "excel.png", "ext" =>  array("xls", "xlsx")),
        array("name" => "mp3", "ico" => "audio.png", "ext" =>  array("mp3", "wav", "flac")),
        array("name" => "html", "ico" => "html.png", "ext" =>  array("html", "htm", "php", "js")),
        array("name" => "zip", "ico" => "compress.png", "ext" =>  array("zip", "rar", "7z", "tar", "bz2", "gz"))
    );

    public function setIcon($ext, $fname) {
        $ico = "img/ftypes/unknown.png";

        for($i=0; $i<count($this->_MIME); $i++) {
            if (in_array(mb_strtolower($ext), $this->_MIME[$i]["ext"])) {

                $this->_ico = "img/ftypes/" . $this->_MIME[$i]["ico"];

                if ($this->_MIME[$i]["ico"] == "image.png") {
                    $pre_img = "upload/_thumb/" . md5($fname);
                    if (is_readable($pre_img)) {
                        $this->_pre_img = $pre_img;
                    } else {
                        $this->_pre_img = $this->_ico;
                    }
                } else {
                    $this->_pre_img = $this->_ico;
                }
            }
        }

        return true;
    }
    // END ICO MIME TYPE

    public function getFolderFiles() {
        return $this->_files;
    }

    public function getFolderDirs() {
        return $this->_dirs;
    }

    public function getFolderTotalSize() {
        return $this->_totalsize;
    }

    public function getPath($tree, $needle) {
        foreach($tree as $part) {
            if ($part["id"] == $needle) {
                $this->_path = $part["path"];
                return true;
            }
        }
        return false;
    }

    public function getPathVar() {
        return $this->_path;
    }

    public function getFiles($dir) {
        $files = array(); $dirs = array(); $i = 0; $k = 0; $total = 0;

        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {

                if ( ($file != '.') and ($file != '..') and ($file != '_thumb') ) {
                    $path = $dir . '/' . $file;

                    if(is_file($path)) {

                        if ($this->_app["session"]->has("sort")) {
                            if ($this->_app["session"]->get("sort") == "date") {
                                $sort_file[] = date("H:i d-m-Y",  filemtime($path));
                            } else if ($this->_app["session"]->get("sort") == "name") {
                                $sort_file[] = $file;
                            } else if ($this->_app["session"]->get("sort") == "size") {
                                $sort_file[] = filesize($path);
                            }
                        }

                        $files[$i]["id"] = md5($path);
                        $files[$i]["name"] = $file;
                        if (mb_strlen($file) > 20) {
                            $files[$i]["shortname"] = mb_substr($file, 0, 10) . ".." . mb_substr($file, mb_strrpos($file, ".")-1, mb_strlen($file)-mb_strrpos($file, ".")+1);
                        } else {
                            $files[$i]["shortname"] = $file;
                        }

                        $ext = mb_substr($files[$i]["name"], mb_strrpos($files[$i]["name"], ".") + 1);
                        $this->setIcon($ext, $path);

                        $files[$i]["ico"] = $this->_ico;
                        $files[$i]["pre_img"] = $this->_pre_img;

                        $size = filesize($path);
                        $total += $size;
                        if (($size / 1024) > 1) { $size = round($size / 1024, 2) . " Kb"; } else { $size = round($size, 2) . " Б"; };
                        if (($size / 1024) > 1) { $size = round($size / 1024, 2) . " Mb"; };
                        $files[$i]["size"] = $size;

                        $files[$i]["date"] = date("H:i d-m-Y",  filemtime($path));

                        $i++;
                    } else {
                        if ($this->_app["session"]->has("sort")) {
                            if ($this->_app["session"]->get("sort") == "date") {
                                $sort_dir[] = date("H:i d-m-Y",  filemtime($path));
                            } else if ($this->_app["session"]->get("sort") == "name") {
                                $sort_dir[] = $file;
                            }
                        }

                        $dirs[$k]["id"] = md5($path);
                        $dirs[$k]["name"] = $file;
                        $dirs[$k]["date"] = date("H:i d-m-Y",  filemtime($path));
                        $k++;
                    }
                }
            }

            closedir($handle);

            if ($this->_app["session"]->has("sort")) {
                if ($this->_app["session"]->get("sort") == "date") {
                    array_multisort($sort_file, SORT_DESC, $files);
                } else if ($this->_app["session"]->get("sort") == "name") {
                    array_multisort($sort_file, $files);
                } else if ($this->_app["session"]->get("sort") == "size") {
                    array_multisort($sort_file, $files);
                }
            }

            $this->_files = $files;

            if ($this->_app["session"]->has("sort")) {
                if ($this->_app["session"]->get("sort") == "date") {
                    array_multisort($sort_dir, SORT_DESC, $dirs);
                } else if ($this->_app["session"]->get("sort") == "name") {
                    array_multisort($sort_dir, $dirs);
                }
            }

            $this->_dirs = $dirs;

            if (($total / 1024) > 1) { $total = round($total / 1024, 2) . " Kb"; } else { $total = round($total, 2) . " Б"; };
            if (($total / 1024) > 1) { $total = round($total / 1024, 2) . " Mb"; };
            $this->_totalsize = $total;

            return true;
        } else {
            $this->_error = "Directory not readable";
        }
    }

    public function hasChildren($path) {
        $flag = false;
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                if ( ($file != '.') and ($file != '..') and ($file != '_thumb') ) {
                    if (is_dir($path . '/' . $file)) {
                        $flag = true;
                    }
                }
            }
        }
        closedir($handle);

        return $flag;
    }


    public function getTree($dir) {
        $array = array(); $i = 0;

        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {

                if ( ($file != '.') and ($file != '..') and ($file != '_thumb') ) {
                    $path = $dir . '/' . $file;

                    if(is_dir($path)) {
                        $array[$i]["text"] = $file;
                        $array[$i]["id"] = md5($path);
                        $array[$i]["path"] = $path;
                        $array[$i]["hasChildren"] = $this->hasChildren($path);
                        $array[$i]["spriteCssClass"] = "folder";

                        $i++;
                    }
                }
            }

            closedir($handle);

            return $array;
        }
    }

    public function rmFiles($file) {
        if (unlink($file)) {
            unlink($this->_app['upload'] . "/_thumb/" .md5($file));
            return true;
        } else {
            return false;
        }
    }

    public function rmDirs($dir) {
        if ($objs = glob($dir."/*")) {
            foreach($objs as $obj) {
                is_dir($obj) ? $this->rmDirs($obj) : unlink($obj);
            }
        }

        rmdir($dir);
    }

    public function getFile($folder, $file) {
        $array = array();
        $path = $folder . "/" . $file;
        if(is_file($path)) {
            $array["name"] = $file;
            $array["id"] = md5($path);
            if (mb_strlen($file) > 20) {
                $array["shortname"] = mb_substr($file, 0, 10) . ".." . mb_substr($file, mb_strrpos($file, ".")-1, mb_strlen($file)-mb_strrpos($file, ".")+1);
            } else {
                $array["shortname"] = $file;
            }

            $ext = mb_substr($array["name"], mb_strrpos($array["name"], ".") + 1);

            $this->setIcon($ext, $path);

            $array["ico"]  = $this->_ico;
            $array["pre_img"] = $this->_pre_img;

            $size = filesize($path);
            if (($size / 1024) > 1) { $size = round($size / 1024, 2) . " Kb"; } else { $size = round($size, 2) . " Б"; };
            if (($size / 1024) > 1) { $size = round($size / 1024, 2) . " Mb"; };
            $array["size"] = $size;

            $array["date"] = date("H:i d-m-Y",  filemtime($path));
        }

        return $array;
    }

    public function getDate($file) {
        return filemtime($file);
    }

    public function mkdir($path, $name) {
        if (mkdir($path . "/" . $name)) {
            return true;
        } else {
            $this->_error = "Create directory error: [1]=" . $path . "/" . $name;

            return false;
        }
    }

    public function past($source, $target) {
        foreach($source as $part) {
            if (copy($part["path"], $target."/".$part["name"])) {

                rename($this->_app['upload'] . "/_thumb/" . $part["id"], $this->_app['upload'] . "/_thumb/" . md5($target."/".$part["name"]));

                if (is_file($part["path"])) {
                    if (!unlink($part["path"])) {
                        $this->_error = "Error remove directory: " . $part["path"];
                    }
                }
                if (is_dir($part["path"])) {
                    if (!rmdir($part["path"])) {
                        $this->_error = "Error remove file: " . $part["path"];
                    }
                }
            } else {
                $this->_error = "Past files impossible, from: ".$part["path"].' to: '.$target."/".$part["name"];
                return false;
            }
        }

        return true;
    }
}