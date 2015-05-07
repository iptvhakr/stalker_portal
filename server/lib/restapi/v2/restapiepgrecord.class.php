<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiEpgRecord extends RESTApiController
{
    protected $name = 'record';
    private   $params;

    public function __construct($nested_params){
        $this->params = $nested_params;
    }

    public function create(RESTApiRequest $request, $parent_id){

        if (empty($this->params['users.id'])){
            throw new RESTBadRequest("User required");
        }

        if (empty($this->params['ch_id'])){
            throw new RESTBadRequest("Channel ID required");
        }

        $user_id    = (int) $this->params['users.id'];
        $ch_id      = (int) $this->params['ch_id'];
        $program_id = (int) $parent_id;

        $user = \Stb::getById($user_id);

        if (empty($user)){
            throw new RESTNotFound("User not found");
        }

        $itv = \Itv::getInstance();

        $user_channels = $itv->getAllUserChannelsIdsByUid($user['id']);

        if (!in_array($ch_id, $user_channels)){
            throw new RESTForbidden("User don't have access to this channel");
        }

        $channel = \Itv::getById($ch_id);

        if (empty($channel)){
            throw new RESTNotFound("Channel not found");
        }

        if (!$channel['allow_pvr']){
            throw new RESTForbidden("Channel does not support PVR");
        }

        $program = \Epg::getById($program_id);

        if (empty($program)){
            throw new RESTNotFound("Program not found");
        }

        if ($program['ch_id'] != $ch_id){
            throw new RESTNotAcceptable("Channel of program mismatch");
        }

        if (strtotime($program['time']) < time()){
            throw new RESTNotAcceptable("Start time in past");
        }

        $pvr = new \RemotePvr();

        try{
            $rec_id = $pvr->startRecDeferredById($program['real_id']);
        }catch (\nPVRException $e){
            throw new RESTServerError($e->getMessage());
        }

        if (!$rec_id){
            return false;
        }

        $recording = $pvr->getById($rec_id);

        return array(
            'id'         => $recording['id'],
            'name'       => $recording['program'],
            'start_time' => strtotime($recording['t_start']),
            'end_time'   => strtotime($recording['t_stop']),
            'ch_id'      => (int) $recording['ch_id'],
            'ch_name'    => $channel['name'],
            'status'     => $recording['started'] ? ($recording['ended'] ? 2 : 1) : 0
        );
    }
}