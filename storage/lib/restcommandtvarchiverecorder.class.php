<?php

class RESTCommandTvArchiveRecorder extends RESTCommand
{
    private $manager;

    public function __construct(){
        $this->manager = new TvArchiveRecorder();
    }

    /**
     * Create recording task
     *
     * @param RESTRequest $request
     * @return bool
     * @throws ErrorException
     */
    public function create(RESTRequest $request){

        $task = $request->getData('task');

        if (empty($task)){
            throw new ErrorException('Empty task');
        }

        return $this->manager->start($task);
    }

    /**
     * Delete recording task
     *
     * @param RESTRequest $request
     * @return bool
     * @throws ErrorException
     */
    public function delete(RESTRequest $request){

        $ch_ids = $request->getIdentifiers();

        if (empty($ch_ids[0])){
            throw new ErrorException('Empty ch_id');
        }

        return $this->manager->stop($ch_ids[0]);
    }
}