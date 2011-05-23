<?php

class RESTCommandStreamRecorder extends RESTCommand
{
    private $manager;

    public function __construct(){
        $this->manager = new StreamRecorder();
    }

    public function get(RESTRequest $request){

        return $this->manager->getTasks();
    }

    public function update(RESTRequest $request){

        $put = $request->getPut();

        if (empty($put)){
            throw new RESTCommandException('HTTP PUT data is empty');
        }

        if (empty($put['action'])){
            throw new RESTCommandException('Action param is empty');
        }

        $identifiers = $request->getIdentifiers();

        if (empty($identifiers)){
            throw new RESTCommandException('Empty identifiers');
        }

        if ($put['action'] == 'start'){
            foreach($identifiers as $identifier){
                $this->manager->startDeferredNow(intval(($identifier)));
            }
            return true;
        }elseif ($put['action'] == 'stop'){
            foreach($identifiers as $identifier){
                $this->manager->stopAndUsrMsg(intval(($identifier)));
            }
            return true;
        }else{
            throw new RESTCommandException('Action is wrong');
        }
    }
}

?>