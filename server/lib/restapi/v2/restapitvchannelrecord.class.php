<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiTvChannelRecord extends RESTApiController
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

        $user_id = $this->params['users.id'];

        $user = \Stb::getById($user_id);

        if (empty($user)){
            throw new RESTNotFound("User not found");
        }

        $itv = \Itv::getInstance();

        $user_channels = $itv->getAllUserChannelsIdsByUid($user['id']);

        if (!in_array($parent_id, $user_channels)){
            throw new RESTForbidden("User don't have access to this channel");
        }

        $channel = \Itv::getById($parent_id);

        if (empty($channel)){
            throw new RESTNotFound("Channel not found");
        }

        if (!$channel['allow_pvr']){
            throw new RESTForbidden("Channel does not support PVR");
        }

        $now_time = time();

        $start_time = (int) $request->getData('start_time');
        $end_time   = (int) $request->getData('end_time');

        if ($start_time && $start_time < $now_time){
            $start_time = $now_time;
        }

        if ($end_time){
            if ($start_time && ($end_time < $start_time) || $end_time < $now_time){
                throw new RESTNotAcceptable("Not acceptable time range");
            }
        }

        $pvr = new \RemotePvr();

        try{
            $rec_id = $pvr->startRecNowByChannelId($channel['id']);
        }catch (\nPVRException $e){
            throw new RESTServerError($e->getMessage());
        }

        if (!$rec_id){
            return false;
        }

        if ($end_time){
            sleep(1); // give some time to dumpstream to startup
            $recorder = new \StreamRecorder();
            $recorder->stopDeferred($rec_id, ceil(($end_time - $now_time)/60));
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