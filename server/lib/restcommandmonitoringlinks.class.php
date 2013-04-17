<?php

class RESTCommandMonitoringLinks extends RESTCommand
{
    private $manager;
    private $allowed_fields;

    public function __construct(){
        $this->manager = Itv::getInstance();
    }

    public function get(RESTRequest $request){

        if (empty($request) || strpos($request->getAccept(), 'text/channel-monitoring-id-url') === false){
            throw new RESTCommandException('Unsupported Accept header, use text/channel-monitoring-id-url');
        }

        $itv_list = Mysql::getInstance()
            ->select('ch_links.*')
            ->from('ch_links')
            ->join('itv', 'ch_links.ch_id', 'itv.id', 'INNER')
            ->where(array('ch_links.enable_monitoring' => 1))
            ->orderby('ch_links.ch_id')
            ->get()
            ->all();

        $itv_list = array_map(function($cmd){

            $cmd['monitoring_url'] = trim($cmd['monitoring_url']);

            if (!empty($cmd['monitoring_url'])){
                $cmd['url'] = $cmd['monitoring_url'];
            }else if (preg_match("/(\S+:\/\/\S+)/", $cmd['url'], $match)){
                $cmd['url'] = $match[1];
            }

            return $cmd;
        }, $itv_list);

        return $itv_list;
    }

    public function update(RESTRequest $request){

        $put = $request->getPut();

        if (empty($put)){
            throw new RESTCommandException('HTTP PUT data is empty');
        }

        $allowed_to_update_fields = array_fill_keys(array('status'), true);

        $data = array_intersect_key($put, $allowed_to_update_fields);

        if (empty($data)){
            throw new RESTCommandException('Update data is empty');
        }

        $ids = $request->getIdentifiers();

        if (empty($ids)){
            throw new RESTCommandException('Empty link id');
        }

        $link_id = intval($ids[0]);

        return Itv::setChannelLinkStatus($link_id, $data['status']);

        //return Mysql::getInstance()->update('itv', $data, array('id' => $channel_id));
    }
}

?>