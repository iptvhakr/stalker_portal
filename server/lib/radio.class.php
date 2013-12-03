<?php
/**
 * Main Radio class.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Radio extends AjaxResponse implements \Stalker\Lib\StbApi\Radio
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

        if (Config::get('enable_tariff_plans') && $all_user_radio_ids != 'all'){
            $result = $result->in('radio.id', $all_user_radio_ids);
        }
        
        $this->setResponseData($result);
        
        return $this->getResponse('prepareData');
    }
    
    public function prepareData(){
        
        return $this->response;
    }

    public function getRawAllUserChannels($uid = null){

        if ($uid){
            if (Config::getSafe('enable_tariff_plans', false)){

                $user = User::getInstance(Stb::getInstance()->id);
                $user_channels = $user->getServicesByType('radio');

                if ($user_channels == 'all'){
                    return Mysql::getInstance()->from('radio')->where(array('status' => 1))->orderby('number');
                }else{
                    return Mysql::getInstance()->from('radio')->where(array('status' => 1))->in('id', $user_channels)->orderby('number');
                }
            }
        }

        return Mysql::getInstance()->from('radio')->where(array('status' => 1))->orderby('number');
    }

    public static function getServices(){
        return Mysql::getInstance()->select('id, name')->from('radio')->get()->all();
    }
}

?>