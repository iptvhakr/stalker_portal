<?php

use Stalker\Lib\Core\Mysql;
use Stalker\Lib\Core\Stb;

class VclubAdvertising implements \Stalker\Lib\StbApi\VclubAdvertising
{
    private $allowed_fields = array('title', 'url', 'must_watch', 'weight', 'status');
    private $total_ads = null;

    public function __construct(){
        $this->allowed_fields = array_fill_keys($this->allowed_fields, true);
    }

    public function add($data){

        $denied_categories = empty($data['denied_categories']) ? array() : $data['denied_categories'];

        $data = array_intersect_key($data, $this->allowed_fields);

        $ad_id = Mysql::getInstance()->insert('vclub_ad', $data)->insert_id();

        if (empty($ad_id)){
            return false;
        }

        if (empty($denied_categories)){
            return $ad_id;
        }

        $denied_categories_data = array_map(function($category_id) use ($ad_id){
            return array(
                'ad_id'       => $ad_id,
                'category_id' => $category_id
            );
        }, $denied_categories);

        Mysql::getInstance()->insert('vclub_ad_deny_category', $denied_categories_data);

        return $ad_id;
    }

    public function updateById($id, $data){

        $new_denied_categories = empty($data['denied_categories']) ? array() : $data['denied_categories'];

        $data = array_intersect_key($data, $this->allowed_fields);

        $update_result = Mysql::getInstance()->update('vclub_ad', $data, array('id' => $id))->result();

        if (!$update_result){
            return false;
        }

        $existed_denied_categories = $this->getDeniedVclubCategoriesForAd($id);

        $need_to_delete = array_diff($existed_denied_categories, $new_denied_categories);

        $need_to_add = array_diff($new_denied_categories, $existed_denied_categories);

        if (!empty($need_to_delete)){
            Mysql::getInstance()->query('delete from vclub_ad_deny_category where ad_id='.$id.' and category_id in ('.implode(', ', $need_to_delete).')');
        }

        if (!empty($need_to_add)){
            $need_to_add = array_map(function($category_id) use ($id){
                return array(
                    'ad_id'       => $id,
                    'category_id' => $category_id
                );
            }, $need_to_add);

            Mysql::getInstance()->insert('vclub_ad_deny_category', array_values($need_to_add));
        }

        return $update_result;
    }

    public function delById($id){

        Mysql::getInstance()->delete('vclub_ad_deny_category', array('ad_id' => $id));

        return Mysql::getInstance()->delete('vclub_ad', array('id' => $id));
    }

    public function getById($id){

        return Mysql::getInstance()->from('vclub_ad')->where(array('id' => $id))->get()->first();
    }

    public function getAll(){

        return Mysql::getInstance()->from('vclub_ad')->get()->all();
    }

    public function getAllWithStatForMonth(){

        return Mysql::getInstance()->query("SELECT vclub_ad.*, SUM(watch_complete) ended, count(vclub_ad_id) started FROM (vclub_ad) LEFT JOIN vclub_ads_log ON (vclub_ad.id=vclub_ad_id) and (vclub_ads_log.added>FROM_UNIXTIME(UNIX_TIMESTAMP(NOW())-2592000)) GROUP BY vclub_ad.id")->all();
    }

    public function getTotalNumber(){

        if ($this->total_ads === null){
            $this->total_ads = Mysql::getInstance()->from('vclub_ad')->where(array('weight!=' => 0, 'status' => 1))->count()->get()->counter();
        }

        return $this->total_ads;
    }

    private function getIdWeightMap($category_id){

        $denied_ads = Mysql::getInstance()
            ->from('vclub_ad_deny_category')
            ->where(array(
                'category_id' => $category_id
            ))
            ->get()
            ->all('ad_id');

        $ads = Mysql::getInstance()->from('vclub_ad')->not_in('id', $denied_ads)->where(array('weight!=' => 0, 'status' => 1))->get()->all();

        $map = array();

        foreach ($ads as $value){
            $map[$value['id']] = $value['weight'];
        }

        return $map;
    }

    public function getOneRandom(){

        $total_ads = $this->getTotalNumber();

        if (empty($total_ads)){
            return null;
        }

        $picked = rand(0, $this->total_ads-1);

        return Mysql::getInstance()->from('vclub_ad')->limit(1, $picked)->get()->first();
    }

    public function getOneWeightedRandom($category_id){

        $map = $this->getIdWeightMap($category_id);

        if (empty($map)){
            return null;
        }

        $ad_id =  $this->getRandomWeightedElement($map);

        if (empty($ad_id)){
            return null;
        }

        return $this->getById($ad_id);
    }

    private function getRandomWeightedElement($array){
        $rand = mt_rand(1, (int) array_sum($array));

        foreach ($array as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) {
                return $key;
            }
        }
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
            'watched_percent' => $total_time == 0 ? 0 : ceil(100*$end_time/$total_time),
            'watched_time'   => $end_time,
            'watch_complete' => $ended,
            'added'          => 'NOW()'
        ))->insert_id();
    }

    public function getDeniedVclubCategoriesForAd($ad_id){
        $category_ids = Mysql::getInstance()
            ->from('vclub_ad_deny_category')
            ->where(array(
                'ad_id' => $ad_id
            ))
            ->get()
            ->all('category_id');

        return $category_ids;
    }
}