<?php

class ImageAutoUpdate
{
    private $settings;

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
        $settings = $this->getSettings();
        return (boolean) $settings['enable'];
    }

    public function getSettings(){
        if (empty($this->settings)){
            $this->checkSettingsStorage();
            $this->settings = Mysql::getInstance()->from("image_update_settings")->get()->first();
        }
        return $this->settings;
    }

    public function setSettings($settings){
        $this->checkSettingsStorage();

        $allowed_fields = array_fill_keys(array('enable', 'require_image_version', 'require_image_date', 'image_version_contains', 'image_description_contains', 'update_type'), true);
        $settings = array_intersect_key($settings, $allowed_fields);

        return Mysql::getInstance()->update("image_update_settings", $settings);
    }

    private function checkSettingsStorage(){

        if (!empty($this->settings)){
            return true;
        }

        $settings = Mysql::getInstance()->from("image_update_settings")->get()->first();

        if (!empty($settings)){
            return true;
        }

        return Mysql::getInstance()->insert("image_update_settings", array('enable' => 0))->insert_id();
    }
}

?>