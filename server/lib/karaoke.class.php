<?php
/**
 * Main Karaoke class.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Karaoke extends AjaxResponse implements \Stalker\Lib\StbApi\Karaoke
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

    public static function getById($id){
        return Mysql::getInstance()->from('karaoke')->where(array('id' => (int) $id))->get()->first();
    }
    
    public function createLink(){
        
        preg_match("/\/media\/(\d+).mpg$/", $_REQUEST['cmd'], $tmp_arr);
        
        $media_id = $tmp_arr[1];

        $res = $this->getLinkByKaraokeId($media_id);
        
        var_dump($res);
        
        return $res;
    }

    public function getLinkByKaraokeId($karaoke_id){

        $master = new KaraokeMaster();

        try {
            $res = $master->play($karaoke_id);
        }catch (Exception $e){
            trigger_error($e->getMessage());
        }

        return $res;
    }

    public function getUrlByKaraokeId($karaoke_id){

        $link = $this->getLinkByKaraokeId($karaoke_id);

        if (empty($link['cmd'])) {
            throw new Exception("Obtaining url failed");
        }

        return $link['cmd'];
    }
    
    private function getData(){
        
        $offset = $this->page * self::max_page_items;
        
        $where = array('status' => 1);
        
        if (!$this->stb->isModerator()){
            $where['accessed'] = 1;
        }
        
        $like = array();
        
        if (@$_REQUEST['abc'] && @$_REQUEST['abc'] !== '*'){
            
            $letter = $_REQUEST['abc'];
            
            if (@$_REQUEST['sortby'] == 'name'){
                $like = array('karaoke.name' => $letter.'%');
            }else{
                $like = array('karaoke.singer' => $letter.'%');
            }
        }
        
        if (@$_REQUEST['search']){
            
            $letters = $_REQUEST['search'];
            
            $search['karaoke.name']   = '%'.$letters.'%';
            $search['karaoke.singer'] = '%'.$letters.'%';
        }
        
        return $this->db
                        ->from('karaoke')
                        ->where($where)
                        ->like($like)
                        ->like($search, 'OR ')
                        ->limit(self::max_page_items, $offset);
    }
    
    public function getOrderedList(){
     
        $result = $this->getData();
        
        if (@$_REQUEST['sortby']){
            $sortby = $_REQUEST['sortby'];
            
            if ($sortby == 'name'){
                $result = $result->orderby('karaoke.name');
            }elseif ($sortby == 'singer'){
                $result = $result->orderby('karaoke.singer');
            }
            
        }else{
            $result = $result->orderby('karaoke.singer');
        }
        
        $this->setResponseData($result);
        
        return $this->getResponse('prepareData');
    }
    
    public function prepareData(){
        
        for ($i = 0; $i < count($this->response['data']); $i++){
            
            if (empty($this->response['data'][$i]['rtsp_url'])){
                $this->response['data'][$i]['cmd'] = '/media/'.$this->response['data'][$i]['id'].'.mpg';
            }else{
                $this->response['data'][$i]['cmd'] = $this->response['data'][$i]['rtsp_url'];
            }
        }
        
        return $this->response;
    }
    
    public function getAbc(){
        
        $abc = array();
        
        foreach ($this->abc as $item){
            $abc[] = array(
                        'id'    => $item,
                        'title' => $item
                     );
        }
        
        return $abc;
    }
    
    public function setClaim(){
        
        return $this->setClaimGlobal('karaoke');
    }

    public function getRawAll(){
        return Mysql::getInstance()
            ->select('karaoke.*, karaoke_genre.title as genre')
            ->from('karaoke')
            ->join('karaoke_genre', 'karaoke.genre_id', 'karaoke_genre.id', 'LEFT')
            ->where(array('status' => 1));
    }
}

?>