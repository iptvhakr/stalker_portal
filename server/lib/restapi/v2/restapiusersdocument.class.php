<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiUsersDocument extends RESTApiDocument
{
    private $fields_map;

    public function __construct(){
        parent::__construct();

        $this->fields_map = array_fill_keys(array('id', "ls", "status", "mac", "fname", "phone", "tariff_plan", "end_date", "account_balance"), true);
    }

    public function get(RESTApiRequest $request, $id){

        $info = new \AccountInfo();
        $user_info = $info->getMainInfo();

        $user_info['id'] = \User::getInstance()->getId();

        return $this->filterDocument($user_info);
    }

    public function filterDocument($document){

        if (empty($document)){
            throw new RESTNotFound("Document not found");
        }

        $document = array_intersect_key($document, $this->fields_map);

        $document['account'] = $document['ls'];
        unset($document['ls']);

        $document['logo'] = \Config::exist('portal_logo_url') ? \Config::get('portal_logo_url') : null;

        if (is_readable(realpath(PROJECT_PATH.'/../new/launcher/img/1080/bg.jpg'))){
            $document['background'] = \Config::getSafe('portal_url', '/stalker_portal/').'new/launcher/img/1080/bg.jpg';
        }else{
            $document['background'] = null;
        }


        return $document;
    }
}