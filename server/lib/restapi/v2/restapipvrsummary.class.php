<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiPvrSummary extends RESTApiController
{
    protected $name = 'pvr-summary';

    public function __construct(){}

    public function get(RESTApiRequest $request, $user_id){

        $recorder = new \StreamRecorder();
        $free = ceil($recorder->checkTotalUserRecordsLength($user_id)/60);
        $total = \Config::getSafe('total_records_length', 600);

        return array('total' => $total, 'free' => $free);
    }
}