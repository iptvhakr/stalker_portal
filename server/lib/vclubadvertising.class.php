<?php

class VclubAdvertising implements \Stalker\Lib\StbApi\VclubAdvertising
{
    private $allowed_fields = array('title', 'url', 'must_watch');
    private $total_ads = null;

    public function __construct(){
        $this->allowed_fields = array_fill_keys($this->allowed_fields, true);
    }

    public function add($data){

        $data = array_intersect_key($data, $this->allowed_fields);

        return Mysql::getInstance()->insert('vclub_ad', $data)->insert_id();
    }

    public function updateById($id, $data){

        $data = array_intersect_key($data, $this->allowed_fields);

        return Mysql::getInstance()->update('vclub_ad', $data, array('id' => $id))->result();
    }

    public function delById($id){

        return Mysql::getInstance()->delete('vclub_ad', array('id' => $id));
    }

    public function getById($id){

        return Mysql::getInstance()->from('vclub_ad')->where(array('id' => $id))->get()->first();
    }

    public function getAll(){

        return Mysql::getInstance()->from('vclub_ad')->get()->all();
    }

    public function getAllWithStatForMonth(){

        /*return Mysql::getInstance()
            ->select('vclub_ad.*, SUM(watch_complete) ended, count(vclub_ad.id) started')
            ->from('vclub_ad')
            ->join('vclub_ads_log', 'vclub_ad.id', 'vclub_ad_id', 'LEFT')
            ->groupby('vclub_ad_id')
            ->where(array('vclub_ads_log.added>' => 'FROM_UNIXTIME(UNIX_TIMESTAMP(NOW())-'.(30*24*3600).')'))
            ->get()
            ->all();*/

        return Mysql::getInstance()->query("SELECT vclub_ad.*, SUM(watch_complete) ended, count(vclub_ad_id) started FROM (vclub_ad) LEFT JOIN vclub_ads_log ON (vclub_ad.id=vclub_ad_id) and (vclub_ads_log.added>FROM_UNIXTIME(UNIX_TIMESTAMP(NOW())-2592000)) GROUP BY vclub_ad.id")->all();
    }

    public function getTotalNumber(){

        if ($this->total_ads === null){
            $this->total_ads = Mysql::getInstance()->from('vclub_ad')->count()->get()->counter();
        }

        return $this->total_ads;
    }

    public function getOneRandom(){

        $total_ads = $this->getTotalNumber();

        if (empty($total_ads)){
            return null;
        }

        $picked = rand(0, $this->total_ads-1);

        return Mysql::getInstance()->from('vclub_ad')->limit(1, $picked)->get()->first();
    }

    public function setAdEndedTime(){

        $ad_id      = (int) $_REQUEST['ad_id'];
        $end_time   = (int) $_REQUEST['end_time'];
        $total_time = (int) $_REQUEST['total_time'];
        $ended      = (int) $_REQUEST['ended'];

        $ad = $this->getById($ad_id);

        if (empty($ad)){
            return false;
        }

        return Mysql::getInstance()->insert('vclub_ads_log', array(
            'title'          => $ad['title'],
            'vclub_ad_id'    => $ad_id,
            'uid'            => Stb::getInstance()->id,
            'watched_percent' => ceil(100*$end_time/$total_time),
            'watched_time'   => $end_time,
            'watch_complete' => $ended,
            'added'          => 'NOW()'
        ))->insert_id();
    }
}