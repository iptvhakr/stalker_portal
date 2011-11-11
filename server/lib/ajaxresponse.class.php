<?php
/**
 * Prepare raw data to AJAX response.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

abstract class AjaxResponse
{
    
    protected $db;
    protected $stb;
    protected $page = 0;
    protected $load_last_page = false;
    protected $cur_page = 0;
    protected $selected_item = 0;
    protected $response = array(
                    'total_items'    => 0,
                    'max_page_items' => 0,
                    'selected_item'  => 0,
                    'cur_page'       => 0,
                    'data'           => array(),
                );
    
    protected $abc = array();
    protected $months = array();
    protected $all_title = '';
    protected $no_ch_info = '';
    const max_page_items = 14;
    
    public function __construct(){
        
        $this->db  = Mysql::getInstance();
        $this->stb = Stb::getInstance();

        $this->response['max_page_items'] = self::max_page_items;

        /// TRANSLATORS: Letters of the alphabet. If the letter is missing - leave ".";
        $this->abc = array_filter(array('*',_('ABC_1l'),_('ABC_2l'),_('ABC_3l'),_('ABC_4l'),_('ABC_5l'),_('ABC_6l'),_('ABC_7l'),_('ABC_8l'),_('ABC_9l'),_('ABC_10l'),_('ABC_11l'),_('ABC_12l'),_('ABC_13l'),_('ABC_14l'),_('ABC_15l'),_('ABC_16l'),_('ABC_17l'),_('ABC_18l'),_('ABC_19l'),_('ABC_20l'),_('ABC_21l'),_('ABC_22l'),_('ABC_23l'),_('ABC_24l'),_('ABC_25l'),_('ABC_26l'),_('ABC_27l'),_('ABC_28l'),_('ABC_29l'),_('ABC_30l'),_('ABC_31l'),_('ABC_32l'),_('ABC_33l')), function($e){return $e != '.';});

        $this->months =  array(_('january'),_('february'),_('march'),_('april'),_('may'),_('june'),_('july'),_('august'),_('september'),_('october'),_('november'),_('december'));

        $this->all_title = _('All');

        $this->no_ch_info = _('[No channel info]');

        $this->page = @intval($_REQUEST['p']);
        
        if ($this->page == 0){
            $this->load_last_page = true;
        }
        
        if ($this->page > 0){
            $this->page--;
        }
    }
    
    /**
     * Append data to response.
     *
     * @param string $key
     * @param mixed $value
     */
    protected function setResponse($key, $value){
        $this->response[$key] = $value;
    }
    
    /**
     * Add main response fields.
     *
     * @param Mysql $query
     */
    protected function setResponseData(Mysql $query){
        
        $query_rows = clone $query;
        
        $this->setResponse('total_items', $query_rows->nolimit()->nogroupby()->noorderby()->count()->get()->counter());
        //$this->setResponse('total_items', $query_rows->nolimit()->noorderby()->get()->count());
        $this->setResponse('cur_page', $this->cur_page);
        $this->setResponse('selected_item', $this->selected_item);
        $this->setResponse('data', $query->get()->all());
    }
    
    /**
     * Apply callback on responce.
     *
     * @param string $callback
     * @return array
     */
    protected function getResponse($callback = ''){
        
        if ($callback && is_callable(array($this, $callback))){
            return call_user_func(array($this, $callback));
        }
        
        return $this->response;
    }
    
    /**
     * Return image dir using image id.
     *
     * @param int $id
     * @return string
     */
    protected function getImgUri($id){
    
        $dir_name = ceil($id/100);
        //$dir_path = Config::get('portal_url').'screenshots/'.$dir_name;
        $dir_path = Config::get('screenshots_url').$dir_name;
        $dir_path .= '/'.$id.'.jpg';
        return $dir_path;
    }
    
    /**
     * Main claim method.
     *
     * @param string $media_type
     */
    protected function setClaimGlobal($media_type){
        
        $id   = intval($_REQUEST['id']);
        $type = $_REQUEST['real_type'];
        
        $this->db->insert('media_claims_log',
                          array(
                              'media_type' => $media_type,
                              'media_id'   => $id,
                              'type'       => $type,
                              'uid'        => $this->stb->id,
                              'added'      => 'NOW()'
                          ));
                          
        $total_media_claims = $this->db->from('media_claims')->where(array('media_type' => $media_type, 'media_id' => $id))->get()->first();
        
        $sound_counter = 0;
        $video_counter = 0;
        
        if ($type == 'video'){
            $video_counter++;
        }else{
            $sound_counter++;
        }
        
        if (!empty($total_media_claims)){
            $this->db->update('media_claims',
                              array(
                                  'sound_counter' => $total_media_claims['sound_counter'] + $sound_counter,
                                  'video_counter' => $total_media_claims['video_counter'] + $video_counter,
                              ),
                              array(
                                  'media_type' => $media_type,
                                  'media_id'   => $id
                              ));
        }else{
            $this->db->insert('media_claims',
                              array(
                                  'sound_counter' => $sound_counter,
                                  'video_counter' => $video_counter,
                                  'media_type'    => $media_type,
                                  'media_id'      => $id
                              ));
        }
        
        $total_daily_claims = $this->db->from('daily_media_claims')->where(array('date' => 'CURDATE()'))->get()->first();
        
        if (!empty($total_daily_claims)){
            $this->db->update('daily_media_claims',
                              array(
                                  $media_type.'_sound' => $total_daily_claims[$media_type.'_sound'] + $sound_counter,
                                  $media_type.'_video' => $total_daily_claims[$media_type.'_video'] + $video_counter
                              ),
                              array('date' => 'CURDATE()'));
        }else{
            $this->db->insert('daily_media_claims',
                              array(
                                  $media_type.'_sound' => $sound_counter,
                                  $media_type.'_video' => $video_counter,
                                  'date'               => 'CURDATE()'
                              ));
        }
    }
}

?>