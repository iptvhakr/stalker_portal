<?php
/**
 * Main Radio class.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Radio extends AjaxResponse implements \Stalker\Lib\StbApi\Radio
{
    public $fav_radio = FALSE;

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

        $fav_str = implode(",", $this->getFav($this->stb->id));

        $result = $this->getData();

        if (@$_REQUEST['sortby']){
            $sortby = $_REQUEST['sortby'];

            if ($sortby == 'name'){

            }elseif ($sortby == 'number'){
                $result = $result->orderby('number');
            }elseif ($sortby == 'fav'){
                $result = $result->orderby('field(id,'.$fav_str.')');
            }

        }else{
            $result = $result->orderby('number');
        }

        if (@$_REQUEST['fav']){
            $result = $result->in('radio.id', $this->getFav($this->stb->id));
        }

        $result = $result->orderby('number');

        if (Config::get('enable_tariff_plans') && $all_user_radio_ids != 'all'){
            $result = $result->in('radio.id', $all_user_radio_ids);
        }
        
        $this->setResponseData($result);
        
        return $this->getResponse('prepareData');
    }
    
    public function prepareData(){
        if (is_array($this->response['data'])) {
            $fav_ids = $this->getFavIds();
            $this->response['data'] = array_map(function($row) use ($fav_ids){
                $row['fav'] = ((int)in_array($row['id'], $fav_ids));
                return $row;
            }, $this->response['data']);

        }
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

    public function setFav($uid = null){

        if (!$uid){
            $uid = $this->stb->id;
        }

        $fav_radio = @$_REQUEST['fav_radio'];

        if (empty($fav_radio)){
            $fav_radio = array();
        }else{
            $fav_radio = explode(",", $fav_radio);
        }

        if (is_array($fav_radio)){
            return $this->saveFav(array_unique($fav_radio), $uid);
        }

        return true;
    }

    public function getAllFavRadio(){
        $fav_str = implode(",", $this->getFav());
        if (empty($fav_str)){
            $fav_str = 'null';
        }
        $fav_radios = $this->db
            ->from('radio')
            ->in('id', $this->getFav())
            ->where(array('status' => 1))
            ->orderby('field(id,'.$fav_str.')');
        $this->setResponseData($fav_radios);

        return $this->getResponse('prepareData');
    }

    public function setFavStatus(){}

    public function getFavIds(){

        $fav_str = implode(",", $this->getFav());

        if (empty($fav_str)){
            $fav_str = 'null';
        }

        $fav_ids = $this->db
            ->from('radio')
            ->in('id', $this->getFav())
            ->where(array('status' => 1))
            ->orderby('field(id,'.$fav_str.')')
            ->get()
            ->all('id');

        return $fav_ids;
    }

    public function getFav($uid = null){

        if (!$uid){
            $uid = $this->stb->id;
        }

        if (empty($this->fav_radio)) {
            $fav_radio_ids_arr = $this->db
                ->select('fav_radio')
                ->from('fav_radio')
                ->where(array('uid' => intval($uid)))
                ->use_caching(array('fav_radio.uid='.intval($uid)))
                ->get()
                ->first('fav_radio');

            if (!empty($fav_radio_ids_arr)) {
                $this->fav_radio = (is_string($fav_radio_ids_arr) ? unserialize($fav_radio_ids_arr): (is_array($fav_radio_ids_arr) ? $fav_radio_ids_arr: array()));
            }
        }

        return $this->fav_radio;
    }

    public function saveFav(array $fav_array, $uid){

        if (empty($uid)){
            return false;
        }

        $fav_ch_str  = serialize($fav_array);

        if (empty($this->fav_radio)) {
            $this->getFav($uid);
        }

        if (empty($this->fav_radio)){
            return $this->db
                ->use_caching(array('fav_radio.uid='.intval($uid)))
                ->insert('fav_radio',
                    array(
                        'uid'     => (int) $uid,
                        'fav_radio'  => $fav_ch_str,
                        'addtime' => 'NOW()'
                    ))->insert_id();
        }else{
            return $this->db
                ->use_caching(array('fav_radio.uid='.intval($uid)))
                ->update('fav_radio',
                    array(
                        'fav_radio'  => $fav_ch_str
                    ),
                    array('uid' => (int) $uid))->result();
        }
    }
}

?>