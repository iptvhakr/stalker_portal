<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiPvrDocument extends RESTApiDocument
{
    private $parent;
    private $id;
    private $recording;

    public function __construct(RESTApiResourcePvr $parent, array $params){
        parent::__construct();

        $this->parent = $parent;

        if (empty($params)){
            return;
        }

        $this->id = (int) $params[0];

        $pvr = new \RemotePvr();

        $recording = $pvr->getById($this->id);

        if (empty($recording)){
            throw new RESTNotFound("Recording not found");
        }

        $this->recording = $recording;
    }

    public function get(RESTApiRequest $request){

        return array(
            'id'         => $this->recording['id'],
            'name'       => $this->recording['program'],
            'start_time' => strtotime($this->recording['t_start']),
            'end_time'   => strtotime($this->recording['t_stop']),
            'ch_id'      => (int) $this->recording['ch_id'],
            'ch_name'    => $this->recording['ch_name'],
            'status'     => $this->recording['started'] ? ($this->recording['ended'] ? 2 : 1) : 0
        );
    }

    public function delete(RESTApiRequest $request){

        $pvr = new \RemotePvr();

        return $pvr->delRecById($this->recording['id']);
    }
}