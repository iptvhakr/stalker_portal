<?php
/**
 * MySQL class
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Database
{
    public $query_counter = 0;
    public $error_counter = 0;
    public $error_str = '';
    public $debug;
    
    public $charset_query = array(
            //'SET character_set_client = cp1251',
            //'SET character_set_connection = cp1251',
            //'SET character_set_database = cp1251',
            //'SET character_set_results = cp1251',
            //'SET character_set_server = cp1251',
            //'SET NAMES cp1251',
            //'SET CHARACTER SET cp1251'
            
            'SET character_set_database = utf8',
            'SET character_set_server = utf8',
            "SET NAMES 'utf8'",
            "SET CHARACTER SET utf8"
    );
    
    static private $instance = NULL;
    
    static function getInstance($dbName){
        if (self::$instance == NULL)
        {
            self::$instance = new Database($dbName);
        }
        return self::$instance;
    }
    
    public function __construct($dbName){
        $this->debug = Debug::getInstance();
        
        if(!mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS)){
            $this->_log("Error: mysql_connect");
            //exit;
        }
        if(!mysql_select_db($dbName)){
            $this->_log("Error: mysql_select_db");
        }
        $this->setCharset();
    }
    
    public function setCharset(){
        foreach ($this->charset_query as $query){
            $result = mysql_query($query);
            if (!$result){
                $this->_log("Error: mysql_query ".mysql_error());
            }
        };
    }
    
    public function executeQuery($sql_query_str){
        $result = mysql_query($sql_query_str);
        if (!$result){
            $this->_log("Error: query: '$sql_query_str' reason: ".mysql_error()." ;");
            $this->error_counter++;
        }
        $this->query_counter++;
        $rs = new ResultSet($result, $sql_query_str);
        if ($result){
            return $rs;
        }else{
            return false;
        }
    }
    
    public function getLastError(){
        return mysql_error();
    }
    
    public function _log($txt){
        $this->debug->parseSQLError($txt);
    }
    
    public function getDebugStr(){
        $str = "query count: ".$this->query_counter."; error count: ".$this->error_counter.";";
        if($this->error_str){
            $str .=  " errors ({$_SERVER['REQUEST_URI']}): ".$this->error_str;
        }
        return $str;
    }
}

class ResultSet{
    
    public $result;
    public $seek_result;
    public $num_rows = 0;
    public $iteration = 0;
    public $sql_query_str = '';
    public $total_rows = 0;
    
    public function __construct($result, $sql_query_str){
        $this->debug = Debug::getInstance();
        $this->result = $result;
        if (strtolower(substr(trim($sql_query_str), 0, 3)) == 'sel'){
            $this->total_rows = mysql_num_rows($this->result);
        }
        $this->sql_query_str = $sql_query_str;
    }
    
    public function getValueByName($rowNr,$colName){
        if ($rowNr < $this->total_rows || $this->total_rows != 0){
            $fp = mysql_data_seek($this->result, $rowNr);
            $row = mysql_fetch_array($this->result, MYSQL_ASSOC);
            return $row[$colName];
        }else{
            return false;
        }
    }
    
    public function getAllValues(){
        $rows = array();
        while ($row = mysql_fetch_array($this->result, MYSQL_ASSOC)) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    public function getValuesByName($colName){
        $rows = array();
        while ($row = mysql_fetch_array($this->result, MYSQL_ASSOC)) {
            $rows[] = $row[$colName];
        }
        return $rows;
    }
    
    public function getValuesByRow($rowNr){
        if ($rowNr < $this->total_rows || $this->total_rows != 0){
            $fp = @mysql_data_seek($this->result, $rowNr);
            $row = mysql_fetch_array($this->result, MYSQL_ASSOC);
            return $row;
        }else{
            return false;
        }
    }
    
    public function getRowCount(){
        $this->num_rows = mysql_num_rows($this->result);
        return $this->num_rows;
    }
    
    public function getCurrentValueByName($colName){
        mysql_data_seek($this->result, $this->iteration-1);
        $row = mysql_fetch_array($this->result, MYSQL_ASSOC);
        return $row[$colName];
    }
    
    public function getCurrentValuesAsHash(){
        $row = mysql_fetch_array($this->result, MYSQL_ASSOC);
        return $row;
    }
    
    public function getLastInsertId(){
        $insert_id = mysql_insert_id();
        return $insert_id;
    }
    
    public function next(){
        if ($this->iteration < $this->total_rows){
            $fp = mysql_data_seek($this->result, $this->iteration);
            if ($fp === false){
                trigger_error ("[mysql_data_seek] sql='$this->sql_query_str'", E_USER_ERROR);
            }
            $this->iteration++;
            return $fp;
        }else{
            return false;
        }
    }
    
    public function _log($txt){
        $this->debug->parseSQLError($txt);
    }
}
?>