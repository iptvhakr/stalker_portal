<?php

namespace Stalker\Lib\RESTAPI\v1;

class RESTCommandMonitoringLinks extends RESTCommand
{
    private $manager;
    private $allowed_fields;

    public function __construct(){
        $this->manager = \Itv::getInstance();
    }

    public function get(RESTRequest $request){

        if (empty($request) || strpos($request->getAccept(), 'text/channel-monitoring-id-url') === false){
            throw new RESTCommandException('Unsupported Accept header, use text/channel-monitoring-id-url');
        }

        $this->setManager($request);

        return $this->manager->getLinksForMonitoring(@$_GET['status']);
    }

    public function update(RESTRequest $request){

        $this->setManager($request);
        $put = $request->getPut();

        if (empty($put)){
            throw new RESTCommandException('HTTP PUT data is empty');
        }

        $allowed_to_update_fields = array_fill_keys(array('status', 'link_id'), true);

        $data = array_intersect_key($put, $allowed_to_update_fields);

        if (empty($data)){
            throw new RESTCommandException('Update data is empty');
        }

        $ids = $request->getIdentifiers();

        if (empty($ids)){
            throw new RESTCommandException('Empty link id');
        }

        $link_id = $ids[0];

        $manager_class = get_class($this->manager);

        return $manager_class::setChannelLinkStatus($link_id, (int) $data['status']);

        //return Mysql::getInstance()->update('itv', $data, array('id' => $channel_id));
    }

    private function setManager(RESTRequest $request){
        $type = $request->getData('type');
        if (empty($type)) {
            $type = (empty($_GET['type']) ? 'itv': $_GET['type']);
        }
        $base_class = ucfirst($type);
        if (class_exists($base_class)) {
            $this->manager = $base_class::getInstance();
        }
    }
}

?>