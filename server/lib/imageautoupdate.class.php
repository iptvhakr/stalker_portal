<?php

use Stalker\Lib\Core\Mysql;

class ImageAutoUpdate
{
    private $id;
    private $settings;
    private static $storage_initialized = false;

    private static $allowed_fields = array(
        'enable',
        'require_image_version',
        'require_image_date',
        'image_version_contains',
        'image_description_contains',
        'hardware_version_contains',
        'update_type',
        'stb_type',
        'prefix'
    );


    /**
     * @param int $id
     * @throws Exception
     */
    public function __construct($id){

        self::checkSettingsStorage();

        $this->id = $id;

        $this->settings = Mysql::getInstance()
            ->from("image_update_settings")
            ->where(array('id' => $id))
            ->get()
            ->first();

        if (empty($this->settings)){
            throw new Exception("Setting not found");
        }
    }

    /**
     * @param int $id
     * @return ImageAutoUpdate
     */
    public static function getById($id){
        return new self($id);
    }

    /**
     * @return array|null
     */
    public static function getAll(){

        self::checkSettingsStorage();

        return Mysql::getInstance()->from("image_update_settings")->orderby('id')->get()->all();
    }

    /**
     * @param string $stb_type
     * @param int $user_id
     * @return array|null
     */
    public static function getSettingByStbType($stb_type, $user_id = 0){

        $user_groups = Mysql::getInstance()
            ->from('stb_in_group')
            ->where(array('uid' => $user_id, 'stb_group_id!=' => 0))
            ->get()
            ->all('stb_group_id');

        $not_in_groups = Mysql::getInstance()
            ->from('image_update_settings')
            ->where(array('stb_type' => $stb_type, 'enable' => 1, 'stb_group_id' => 0))
            ->get()
            ->all();

        $in_groups = Mysql::getInstance()
            ->from('image_update_settings')
            ->where(array('stb_type' => $stb_type, 'enable' => 1))
            ->in('stb_group_id', $user_groups)
            ->get()
            ->all();

        return array_merge($not_in_groups, $in_groups);
    }

    public static function create($settings){

        $allowed_fields = array_fill_keys(self::$allowed_fields, true);
        $settings = array_intersect_key($settings, $allowed_fields);

        return Mysql::getInstance()->insert("image_update_settings", $settings);
    }

    public function enable(){
        if ($this->isEnabled()){
            return true;
        }

        return $this->setSettings(array('enable' => 1));
    }

    public function disable(){
        if (!$this->isEnabled()){
            return true;
        }

        return $this->setSettings(array('enable' => 0));
    }

    public function toggle(){
        if ($this->isEnabled()){
            return $this->disable();
        }else{
            return $this->enable();
        }
    }

    public function isEnabled(){
        return (boolean) $this->settings['enable'];
    }

    public function setSettings($settings){

        $allowed_fields = array_fill_keys(self::$allowed_fields, true);
        $settings = array_intersect_key($settings, $allowed_fields);

        return Mysql::getInstance()->update("image_update_settings", $settings, array('id' => $this->id));
    }

    public function delete(){
        return Mysql::getInstance()->delete('image_update_settings', array('id' => $this->id));
    }

    private static function checkSettingsStorage(){

        if (self::$storage_initialized){
            return false;
        }

        self::$storage_initialized = true;

        $settings = Mysql::getInstance()->from("image_update_settings")->get()->first();

        if (!empty($settings)){
            return true;
        }

        return Mysql::getInstance()->insert("image_update_settings", array('enable' => 0))->insert_id();
    }
}

?>