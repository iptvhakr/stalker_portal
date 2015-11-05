<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiUserSettings extends RESTApiController
{

    protected $name = 'settings';
    private $fields_map;

    public function __construct(){
        $this->fields_map = array_fill_keys(array("parent_password", "theme"), true);
    }

    public function get(RESTApiRequest $request, $parent_id){

        $user = \Stb::getById($parent_id);

        return $this->filter($user);
    }

    public function update(RESTApiRequest $request, $parent_id){

        $allowed_for_update = array_fill_keys(array("parent_password", "theme"), true);

        $data = $request->getData();

        if (empty($data)){
            throw new RESTBadRequest("Update data is empty");
        }

        $data = array_intersect_key($data, $allowed_for_update);

        if (empty($data)){
            throw new RESTBadRequest("Update data is empty");
        }

        if (!empty($data['theme'])){
            $themes = \Middleware::getThemes();

            if (!isset($themes[$data['theme']])){
                throw new RESTBadRequest("Theme '".$data['theme']."' is not supported");
            }
        }

        return \Stb::updateById($parent_id, $data);
    }

    private function filter($profile){

        if (empty($profile)){
            throw new RESTNotFound("User not found");
        }

        $profile = array_intersect_key($profile, $this->fields_map);

        $themes = \Middleware::getThemes();

        $profile['theme'] = empty($profile['theme']) || !array_key_exists($profile['theme'], $themes)
            ? \Mysql::getInstance()->from('settings')->get()->first('default_template')
            : $profile['theme'];

        $profile['themes'] = $themes;

        return $profile;
    }
}