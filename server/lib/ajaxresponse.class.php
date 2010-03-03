<?php
/**
 * Prepare raw data to AJAX response.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class AjaxResponse
{
    
    protected $db;
    protected $stb;
    protected $page = 0;
    protected $response = array(
                    'total_items'    => 0,
                    'max_page_items' => MAX_PAGE_ITEMS,
                    'selected_item'  => 0,
                    'cur_page'       => 0,
                    'data'           => array(),
                );
    
    protected function __construct(){
        $this->db  = Mysql::getInstance();
        $this->stb = Stb::getInstance();
        
        $this->page = @intval($_REQUEST['p']);
        
        if ($this->page > 0){
            $this->page--;
        }
    }
    
    protected function setResponse($key, $value){
        $this->response[$key] = $value;
    }
    
    protected function setResponseData(Mysql $query){
        
        $query_rows = clone $query;
        
        $this->setResponse('total_items', $query_rows->nolimit()->get()->count_rows());
        $this->setResponse('data', $query->get()->all());
    }
    
    protected function getResponse($callback){
        
        if ($callback && is_callable(array($this, $callback))){
            return call_user_func(array($this, $callback));
        }
        
        return $this->response;
    }
}

?>