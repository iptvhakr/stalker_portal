<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiResourcePvr extends RESTApiCollection
{
    private $user_id;
    protected $params_map = array("users" => "users.id");

    public function __construct(array $nested_params, array $external_params){
        parent::__construct($nested_params, $external_params);

        $this->document = new RESTApiPvrDocument($this, $this->external_params);
        $this->document->controllers->add(new RESTApiPvrLink($this->nested_params));
        $this->document->controllers->add(new RESTApiPvrStop($this->nested_params));

        if (!empty($this->nested_params['users.id'])){
            $user_id = $this->nested_params['users.id'];

            $user = \Stb::getById($user_id);

            if (empty($user)){
                throw new RESTNotFound("User not found");
            }

            $this->user_id = $user['id'];
        }else{
            throw new RESTNotAcceptable("User ID required");
        }
    }

    public function getCount(RESTApiRequest $request){

    }

    public function get(RESTApiRequest $request){

        $pvr = new \RemotePvr();

        return $this->filter($pvr->prepareQuery()->where(array('uid' => $this->user_id))->get()->all());
    }

    public function filter($list){

        $user_id = $this->user_id;

        $list = array_map(function($recording) use ($user_id){

            $status = $recording['started'] ? ($recording['ended'] ? 2 : 1) : 0;

            return array(
                'id'         => (int) $recording['id'],
                'name'       => $recording['program'],
                'start_time' => strtotime($recording['t_start']),
                'end_time'   => strtotime($recording['t_stop']),
                'ch_id'      => (int) $recording['ch_id'],
                'ch_name'    => $recording['ch_name'],
                'status'     => $status,
                'downloadable' => $status == 2 && in_array('downloads', \Stb::getAvailableModulesByUid($user_id)) ? 1 : 0
            );
        }, $list);

        return $list;
    }
}