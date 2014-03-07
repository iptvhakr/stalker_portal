<?php

class RESTCommandRecorder extends RESTCommand
{
    private $manager;

    public function __construct(){
        $this->manager = new Recorder();
    }

    /**
     * Start recording
     *
     * @throws ErrorException
     * @param RESTRequest $request
     * @return string
     */
    public function create(RESTRequest $request){

        $url    = $request->getData('url');
        $rec_id = intval($request->getData('rec_id'));
        $start_delay = intval($request->getData('start_delay'));
        $duration    = intval($request->getData('duration'));

        if (empty($url)){
            throw new ErrorException('Empty url');
        }

        if (empty($rec_id)){
            throw new ErrorException('Empty rec_id');
        }

        if (empty($duration)){
            throw new ErrorException('Empty recording duration');
        }

        if ($start_delay < 0){
            $start_delay = 0;
        }

        return $this->manager->start($url, $rec_id, $start_delay, $duration);
    }

    /**
     * Stop recording
     *
     * @throws ErrorException
     * @param RESTRequest $request
     * @return bool
     */
    public function update(RESTRequest $request){

        $identifiers = $request->getIdentifiers();

        if (empty($identifiers[0])){
            throw new ErrorException('Empty rec_id');
        }

        $rec_id = intval($identifiers[0]);
        $stop_time = intval($request->getData('stop_time'));
        if ($stop_time){
            return $this->manager->updateStopTime($rec_id, $stop_time);
        }else{
            return $this->manager->stop($rec_id);
        }
    }

    /**
     * Delete record file
     *
     * @throws ErrorException
     * @param RESTRequest $request
     * @return bool
     */
    public function delete(RESTRequest $request){

        //$filename = $request->getData('filename');
        $files = $request->getIdentifiers();

        if (empty($files[0])){
            throw new ErrorException('Empty filename');
        }

        return $this->manager->delete($files[0]);
    }
}

?>