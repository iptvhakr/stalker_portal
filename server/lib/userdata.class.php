<?php

class UserData extends Data 
{
    
    public function __construct($action){
        parent::__construct($action);
    }
    
    public function getProfile(){
        
        $hd  = @$_REQUEST['hd']  || 0;
        $ver = @$_REQUEST['ver'] || '';
        
        if (!$this->stb->id){
            
            $uid = $this->addData('users', array(
                    'mac'  => $this->stb->mac,
                    'name' => substr($this->stb->mac, 12, 16)
                    
                ));
            $this->stb->setId($uid);
            
            $uid = $this->addData('updated_places', array('uid' => $this->stb->id));
            
        }
        
        $this->setData('users', array(
                'last_start' => 'NOW()',
                'keep_alive' => 'NOW()',
                'version'    => $ver,
                'hd'         => $hd,
            ),
            array('id' => $this->stb->id));
            
        $master = new VideoMaster();
        
        $data = $this->stb->params;
        $data['storages'] = $master->getStoragesForStb();
        
        $cur_weather = new Curweather();
        $data['cur_weather'] = $cur_weather->getData();
        
        $last_id_arr = $this->getData('last_id', array('ident' => $this->stb->mac));
        
        if (is_array($last_id_arr) && !empty($last_id_arr)){
            $last_id = $last_id_arr[0]['last_id'];
        }else{
            $last_id = 0;
        }
        
        $data['last_itv_id'] = intval($last_id);
        
        $master->checkAllHomeDirs();
        
        $updated_places_arr = $this->getData('updated_places', array('uid' => $this->stb->id));
        
        $data['updated'] = array();
        $data['updated']['anec'] = intval($updated_places_arr[0]['anec']);
        $data['updated']['vclub'] = intval($updated_places_arr[0]['vclub']);
        
        $resp['data'] = $data;
        return $resp;
    }
    
    
    
}
?>