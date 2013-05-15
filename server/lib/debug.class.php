<?php
/**
 * Debug class
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Debug
{
    public $php_err_str     = '';
    public $php_err_counter = 0;
    public $sql_err_str     = '';
    public $sql_err_counter = 0;
    
    static private $instance = NULL;
    
    static function getInstance(){
        if (self::$instance == NULL)
        {
            self::$instance = new Debug();
        }
        return self::$instance;
    }
    
    public function parsePHPError($num, $err ,$file ,$line){
        if($num != E_NOTICE){
            $this->php_err_str .= " txt: ".$err."; file: ".$file."; line: ".$line."; ";
            $this->php_err_counter++;
        }
    }
    
    public function parseSQLError($err){
        $this->sql_err_str .= $err;
        $this->sql_err_counter++;
    }
    
    public function getErrorStr(){
        $str  = "php errors: ".$this->php_err_counter."; sql errors: ".$this->sql_err_counter.";";
        if ($this->php_err_str){
            $str .= " php err str: ".$this->php_err_str.";";
        }
        if ($this->sql_err_str){
            $str .= " sql err str: ".$this->sql_err_str;
        }
        return $str;
    }
}
?>