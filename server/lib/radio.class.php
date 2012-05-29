<?php
/**
 * Main Radio class.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Radio extends AjaxResponse
{
    public static $instance = NULL;
    
    public static function getInstance(){
        if (self::$instance == NULL)
        {
            self::$instance = new self;
        }
        return self::$instance;
    }
    
    public function __construct(){
        parent::__construct();
    }
    
    private function getData(){
        
        $offset = $this->page * self::max_page_items;
        
        $where = array();
        
        if (!$this->stb->isModerator()){
            $where['status'] = 1;
        }
        
        return $this->db
                        ->from('radio')
                        ->where($where)
                        ->limit(self::max_page_items, $offset);
    }
    
    public function getOrderedList(){

        $user = User::getInstance($this->stb->id);
        $all_user_radio_ids = $user->getServicesByType('radio');
        if ($all_user_radio_ids === null){
            $all_user_radio_ids = array();
        }
        
        $result = $this->getData();
        
        $result = $result->orderby('number');

        if (Config::get('enable_tariff_plans')){
            $result = $result->in('radio.id', $all_user_radio_ids);
        }
        
        $this->setResponseData($result);
        
        return $this->getResponse('prepareData');
    }
    
    public function prepareData(){
        
        return $this->response;
    }

    public static function getServices(){
        return Mysql::getInstance()->select('id, name')->from('radio')->get()->all();
    }
}

?>