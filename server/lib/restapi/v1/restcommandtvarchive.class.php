<?php

namespace Stalker\Lib\RESTAPI\v1;

class RESTCommandTvArchive extends RESTCommand
{
    private $manager;

    public function __construct(){
        $this->manager = new \TvArchive();
    }

    public function get(RESTRequest $request){

        $ids = $request->getIdentifiers();

        if (empty($ids[0])){
            throw new \ErrorException('Empty storage name');
        }

        return $this->manager->getAllTasks($ids[0], true);
    }

    public function update(RESTRequest $request){

        $ids = $request->getIdentifiers();

        if (empty($ids[0]) || intval($ids[0]) == 0){
            throw new \ErrorException('Empty channel id');
        }

        $data = $request->getData();

        if (array_key_exists('start_time', $data)){
            $this->manager->updateStartTime(intval($ids[0]), $data['start_time']);
        }

        if (array_key_exists('end_time', $data)){
            $this->manager->updateEndTime(intval($ids[0]), $data['end_time']);
        }

        return true;
    }
}

?>