<?php

namespace Stalker\Lib\RESTAPI\v2;

class RESTApiEpgLink extends RESTApiController
{
    protected $name = 'link';
    private   $params;

    public function __construct($nested_params){
        $this->params = $nested_params;
    }

    public function get(RESTApiRequest $request, $parent_id){

        $epg = \Epg::getById(intval($parent_id));

        if (empty($epg)){
            throw new RESTNotFound("Program not found");
        }

        $channel = \Itv::getChannelById(intval($epg['ch_id']));

        if (empty($channel)){
            throw new RESTNotFound("Channel not found");
        }

        if ($channel["enable_tv_archive"] != 1){
            throw new RESTForbidden("This channel does not have tv archive");
        }

        try{
            $archive = new \TvArchive();
            $url = $archive->getUrlByProgramId(intval($epg['id']));
        }catch(\StorageSessionLimitException $e){
            throw new RESTTemporaryUnavailable("Session limit");
        }catch(\Exception $e){
            throw new RESTServerError("Failed to obtain url");
        }

        return $url;
    }
}