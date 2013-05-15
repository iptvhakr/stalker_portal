<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiResourceEpg extends RESTApiCollection
{
    protected $params_map = array("tv-channels" => "ch_id");
    private   $manager;
    private   $fields_map;

    public function __construct(array $nested_params, array $external_params){
        parent::__construct($nested_params, $external_params);

        $this->document = new RESTApiEpgDocument();
        $this->document->controllers->add(new RESTApiEpgLink($this->nested_params));

        $this->manager = new \Epg();

        $this->fields_map = array_fill_keys(array('id', "name"), true);
    }

    public function getCount(RESTApiRequest $request){
        throw new RESTNotFound("Resource not support count");
    }

    public function get(RESTApiRequest $request){
        //throw new RESTNotAllowedMethod("Please use /tv-channel/[ch_id]/epg instead");

        if (empty($this->nested_params['ch_id'])){
            throw new RESTBadRequest("ch_id required");
        }

        $ch_id = (int) $this->nested_params['ch_id'];

        $channel = \Itv::getChannelById($ch_id);

        if (empty($channel)){
            throw new RESTNotFound("Channel not found");
        }

        $from = (int) $request->getParam('from');
        $to   = (int) $request->getParam('to');

        $next = (int) $request->getParam('next');

        if (!empty($next)){
            return $this->filter($this->manager->getCurProgramAndFewNext($channel['id'], $next));
        }else{
            $from = empty($from) ? "" : date("Y-m-d H:i:s", $from);
            $to   = empty($to)   ? "" : date("Y-m-d H:i:s", $to);

            $epg = $this->manager->getEpgForChannelsOnPeriod(array($channel['id']), $from, $to);

            return $this->filter($epg[$channel['id']]);
        }
    }

    private function filter($epg){

        $fields_map = $this->fields_map;

        $epg = array_map(function($program) use ($fields_map){

            $new_program = array_intersect_key($program, $fields_map);
            $new_program['start']      = (int) $program['start_timestamp'];
            $new_program['end']        = (int) $program['stop_timestamp'];
            $new_program['in_archive'] = (int) $program['mark_archive'];

            return $new_program;
        }, $epg);

        return $epg;
    }
}