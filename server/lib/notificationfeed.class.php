<?php

use \Stalker\Lib\Core\Mysql;

class NotificationFeed
{
    private $feed_url = 'https://not.ministra.com/feed';

    /**
     * @param bool $only_not_read
     * @return integer
     */
    public function getCount($only_not_read = true){

        $items = Mysql::getInstance()->from('notification_feed')
            ->where(array(
                'delay_finished_time<=' => date(Mysql::DATETIME_FORMAT)
            ))
            ->count();

        if ($only_not_read){
            $items->where(array('`read`' => 0));
        }

        return (int)$items->get()->counter();
    }

    /**
     * @param bool $only_not_read
     * @return NotificationFeedItem[]
     */
    public function getItems($only_not_read = true){

        $items = Mysql::getInstance()->from('notification_feed')
            ->where(array(
                'delay_finished_time<=' => date(Mysql::DATETIME_FORMAT)
            ))
            ->orderby('pub_date DESC, guid', 'DESC');

        if ($only_not_read){
            $items->where(array('`read`' => 0));
        }

        $items = $items->get()->all();

        $items = array_map(function ($item){
            return new NotificationFeedItem($item);
        }, $items);

        return $items;
    }

    public function sync() {

        $language = Mysql::getInstance()->from('administrators')->where(array('login' => 'admin'))->get()->first('language');

        if (!$language){
            $language = 'en';
        }

        $feed_url = $this->feed_url.(strpos($this->feed_url, '?') ? '&' : '?').'lang='.$language;

        $content = file_get_contents($feed_url);

        if (!$content){
            return false;
        }

        $feed = simplexml_load_string($content);

        if (!$feed){
            return false;
        }

        $result = true;

        foreach ($feed->channel->item as $item){

            $item_arr = array(
                'title' => strval($item->title),
                'description' => strval($item->description),
                'link' => strval($item->link),
                'category' => strval($item->category),
                'pub_date' => date(Mysql::DATETIME_FORMAT, strtotime(strval($item->pubDate))),
                'guid' => strval($item->guid),
            );

            $notification = new NotificationFeedItem($item_arr);
            $result = $notification->sync() && $result;
        }

        return $result;
    }

    /**
     * @param $guid
     * @return bool|NotificationFeedItem
     */
    public function getItemByGUId($guid){

        $item = Mysql::getInstance()->from('notification_feed')->where(array('guid' => $guid))->get()->first();

        if (!$item){
            return false;
        }

        return new NotificationFeedItem($item);
    }
}

class NotificationFeedItem{

    private $_id;
    private $title;
    private $description;
    private $link;
    private $category;
    private $pub_date;
    private $guid;
    private $read;

    public function __construct($item) {

        if (isset($item['id'])){
            $this->_id = (int)$item['id'];
        }

        if (isset($item['title'])){
            $this->title = $item['title'];
        }

        if (isset($item['description'])){
            $this->description = $item['description'];
        }

        if (isset($item['link'])){
            $this->link = $item['link'];
        }

        if (isset($item['category'])){
            $this->category = $item['category'];
        }

        if (isset($item['pub_date'])){
            $this->pub_date = $item['pub_date'];
        }

        if (isset($item['guid'])){
            $this->guid = $item['guid'];
        }

        if (isset($item['read'])){
            $this->read = $item['read'] == 1 ? true : false;
        }
    }

    /**
     * @return bool
     */
    public function sync(){

        if ($this->_id){
            $db_item = Mysql::getInstance()->from('notification_feed')->where(array('id' => $this->_id))->get()->first();
        }elseif($this->guid){
            $db_item = Mysql::getInstance()->from('notification_feed')->where(array('guid' => $this->guid))->get()->first();
        }else{
            return false;
        }

        if (empty($db_item)){

            $this->read = 0;

            $this->_id = Mysql::getInstance()->insert('notification_feed',
                array(
                    'title' => $this->title,
                    'description' => $this->description,
                    'link' => $this->link,
                    'category' => $this->category,
                    'pub_date' => $this->pub_date,
                    'guid' => $this->guid,
                    'read' => $this->read,
                    'added' => 'NOW()'
                ))->insert_id();

            return $this->_id ? true : false;

        }elseif($db_item['title'] != $this->title
            || $db_item['description'] != $this->description
            || $db_item['link'] != $this->link
            || $db_item['category'] != $this->category
            || $db_item['pub_date'] != $this->pub_date
            || $db_item['guid'] != $this->guid
        ){
            return Mysql::getInstance()->update('notification_feed',
                array(
                    'title' => $this->title,
                    'description' => $this->description,
                    'link' => $this->link,
                    'category' => $this->category,
                    'pub_date' => $this->pub_date,
                    'guid' => $this->guid,
                    'added' => 'NOW()'
                ), array(
                    'id' => $db_item['id']
                ))->result();
        }else{
            return false;
        }
    }

    /**
     * @return string
     */
    public function getTitle(){
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription(){
        return $this->description;
    }

    /**
     * @return string
     */
    public function getLink(){
        return $this->link;
    }

    /**
     * @return string
     */
    public function getCategory(){
        return $this->category;
    }

    /**
     * @return string
     */
    public function getPubDate(){
        return $this->pub_date;
    }

    /**
     * @return string
     */
    public function getGUId(){
        return $this->guid;
    }

    /**
     * @return bool
     */
    public function getRead(){
        return $this->read;
    }

    /**
     * @param int $read
     * @return bool
     */
    public function setRead($read = 0){

        $this->read = $read;

        return Mysql::getInstance()->update('notification_feed',
            array('`read`' => $this->read),
            array('id' => $this->_id))
            ->result();
    }

    /**
     * @param int $minutes
     * @return bool
     */
    public function setDelay($minutes){

        $this->read = 0;

        return Mysql::getInstance()->update('notification_feed',
            array(
                'delay_finished_time' => date(Mysql::DATETIME_FORMAT, time() + $minutes * 60),
                '`read`' => 0,
                ),
            array('id' => $this->_id))
            ->result();
    }

}