<?php
/**
 * Mastermind class.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Mastermind extends AjaxResponse implements \Stalker\Lib\StbApi\Mastermind
{
    private static $instance = NULL;
    
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
    
    public function addLog(){
        
        $tries = $_REQUEST['tries'];
        $total_time = $_REQUEST['total_time'];
        $points = 1;
        
        if ($tries <= 7 && $total_time < 600){
            $points = 3;
        }else if ($tries <= 10 && $total_time < 600){
            $points = 2;
        }
        
        return $this->db->insert('mastermind_wins',
                                 array(
                                     'uid' => $this->stb->id,
                                     'tries' => $tries,
                                     'total_time' => $total_time,
                                     'points' => $points,
                                     'added' => 'NOW()'
                                 ))->insert_id();
    }
    
    private function getOffset($where = array()){
        
        if (!$this->load_last_page){
            return $this->page * self::max_page_items;
        }
        
        $uid_points = $this->db->select('SUM(points) as sum_points')->from('mastermind_wins')->where(array('uid' => $this->stb->id))->get()->first('sum_points');
        
        var_dump('!!!',$uid_points);
        
        if ($uid_points > 0){
            
            $res = $this->db
                     ->select('SUM(points) as sum_points,uid,MIN(tries) as min_tries, MIN(total_time) as min_time')
                     ->from('mastermind_wins')
                     ->groupby('uid')
                     ->orderby('sum_points', 'desc')
                     ->orderby('min_tries')
                     ->orderby('min_time')
                     ->get()
                     ->all();
            
            $n = 1;
            
            foreach ($res as $item){
                
                if ($item['uid'] != $this->stb->id){
                    $n++;
                }else{
                    break;
                }
            }
            
            $this->cur_page = ceil($n/self::max_page_items);
            $this->page = $this->cur_page-1;
            $this->selected_item = $n - ($this->cur_page-1)*self::max_page_items;
            
        }else{
            $this->page = 0;
            $this->cur_page = 1;
        }
        
        $page_offset = ($this->cur_page-1)*self::max_page_items;
        
        if ($page_offset < 0){
            $page_offset = 0;
        }
        
        return $page_offset;
    }
    
    private function getData(){
        
        
        $offset = $this->getOffset();
        
        var_dump($offset);
        
        return $this->db
                        ->select('uid, name, count(uid) as games, MIN(tries) as min_tries, MIN(total_time) as min_time, SUM(points) as sum_points')
                        ->from('mastermind_wins')
                        ->join('users', 'mastermind_wins.uid', 'users.id', 'INNER')
                        ->groupby('uid')
                        ->orderby('sum_points', 'desc')
                        ->orderby('min_tries')
                        ->orderby('min_time')
                        ->limit(self::max_page_items, $offset);
        
    }
    
    public function getRating(){
        
        $result = $this->getData();
        
        $this->setResponseData($result);
        
        $this->response['total_items'] = count($this->db->count()->from('mastermind_wins')->join('users', 'mastermind_wins.uid', 'users.id', 'INNER')->groupby('uid')->get()->all());
        
        return $this->getResponse('prepareData');
    }
    
    public function prepareData(){
        
        $place = ($this->page) * self::max_page_items + 1;
        
        for ($i = 0; $i < count($this->response['data']); $i++){
            
            $this->response['data'][$i]['place'] = strval($place);
            
            $place++;
        }
        
        return $this->response;
    }
}

?>