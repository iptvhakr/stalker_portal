<?php

class RESTCommandStreamRecorder extends RESTCommand
{
    private $manager;

    public function __construct(){
        $this->manager = new StreamRecorder();
    }

    public function get(RESTRequest $request){

        $identifiers = $request->getIdentifiers();

        if (empty($identifiers)){
            return $this->manager->getTasks();
        }

        return $this->manager->getRecordingInfo($identifiers[0]);
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

        if ($put['action'] == 'started'){
            foreach($identifiers as $identifier){
                $this->manager->setStarted(intval(($identifier)));
            }
            return true;
        }elseif ($put['action'] == 'ended'){
            foreach($identifiers as $identifier){
                $this->manager->setEnded(intval(($identifier)));
            }
            return true;
        }else{
            throw new RESTCommandException('Action is wrong');
        }
    }
}

?>